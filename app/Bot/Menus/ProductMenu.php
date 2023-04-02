<?php

namespace App\Bot\Menus;

use App\Magento\Repository\MageRepository;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Attributes\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Message\Message;

class ProductMenu extends AbstractMenu
{
    private int $curPage = 1;

    public function __construct(protected MageRepository $mageRepository)
    {
        parent::__construct();
    }

    public function start(Nutgram $bot)
    {
        $this->clearButtons();

        $categoryId = $bot->getData('category_id');
        $keyword = $bot->getData('keyword') ?? '';
        if ($categoryId) {
            $product = $this->mageRepository->getProducts($categoryId, 1, $this->curPage)->last();
        } elseif ($keyword) {
            $product = $this->mageRepository->getProductsLike('name', $keyword, 1, $this->curPage)->last();
        }

        $text = 'Товаров в этой категории нет';
        if (isset($product)) {
            $this->addButtonRow(InlineKeyboardButton::make("Закрепить", callback_data: "category=$categoryId,curPage=1,keyword=$keyword@handlePin"));
            if (isset($product->media_gallery_entries[0]->file)) {
                $this->bot->setData('photo', config('global.magento_url')."/pub/media/catalog/product".$product->media_gallery_entries[0]->file);
            }
            $this->bot->setData('sku', $product->sku);
            $this->bot->setData('keyword', $keyword);
            $text = "<b>$product->name</b>\n"
                . "Art.: $product->sku\n"
                . "Цена: $product->price" . config('global.currency') . "\n"
                . "Ссылка: " . config('global.magento_url') . "/catalog/product/view/id/" . $product->id;
            $this->addButtonRow(InlineKeyboardButton::make("Дальше", callback_data: "category=$categoryId,curPage=1,action=next,keyword=$keyword@handleButton"));
        }

        if (isset($data['keyword'])) {
            $this->addButtonRow(InlineKeyboardButton::make("\xF0\x9F\x94\x99", callback_data: 'start_search_menu'));
        } else {
            $this->addButtonRow(InlineKeyboardButton::make("\xF0\x9F\x94\x99", callback_data: 'categories'));
        }

        $this->menuText($text, ['parse_mode' => ParseMode::HTML])->showMenu();
    }

    public function handleButton(Nutgram $bot) {
        $data = $this->getDataFromString($bot->callbackQuery()->data);

        switch ($data['action']) {
            case 'prev':
                $this->handlePrev($bot);
                break;
            case 'next':
                $this->handleNext($bot);
                break;
        }
    }

    public function handleNext(Nutgram $bot)
    {
        $this->clearButtons();
        $data = $this->getDataFromString($bot->callbackQuery()->data);
        $data['curPage']++;

        if ($botPage = $bot->getData('curPage')) {
            $data['curPage'] = $botPage;
        }

        Log::debug("Next" . $data['curPage'] . $data['keyword']);

        if ($data['category']) {
            $product = $this->mageRepository->getProducts($data['category'], 1, $data['curPage'])->last();
        } elseif ($data['keyword']) {
            $product = $this->mageRepository->getProductsLike('name', $data['keyword'], 1, $data['curPage'])->last();
        }

        $text = 'Больше товаров нет!';
        $ans = false;
        if ($product) {
            $this->addButtonRow(InlineKeyboardButton::make(
                "Закрепить",
                callback_data: "category=" . $data['category']
                    . ",curPage=".$data['curPage']
                    . ",keyword=".$data['keyword']."@handlePin"
            ));
            if (isset($product->media_gallery_entries[0]->file)) {
                $this->bot->setData('photo', config('global.magento_url')."/pub/media/catalog/product".$product->media_gallery_entries[0]->file);
            }
            $this->bot->setData('sku', $product->sku);
            $text = "<b>$product->name</b>\n"
                . "Art.: $product->sku\n"
                . "Цена: $product->price" . config('global.currency') . "\n"
                . "Ссылка: " . config('global.magento_url') . "/catalog/product/view/id/" . $product->id;
            $ans = true;
        }
        if ($ans) {
            $this->addButtonRow(
                InlineKeyboardButton::make("Назад", callback_data: "category=".$data['category'].",curPage=".$data['curPage'].",action=prev,keyword=".$data['keyword']."@handleButton"),
                InlineKeyboardButton::make("Дальше", callback_data: "category=".$data['category'].",curPage=".$data['curPage'].",action=next,keyword=".$data['keyword']."@handleButton")
            );
        } else {
            $this->addButtonRow(
                InlineKeyboardButton::make("Назад", callback_data: "category=".$data['category'].",curPage=".$data['curPage'].",action=prev,keyword=".$data['keyword']."@handleButton")
            );
        }
        Log::debug($text);

        if (isset($data['keyword'])) {
            $this->addButtonRow(InlineKeyboardButton::make("\xF0\x9F\x94\x99", callback_data: 'start_search_menu'));
        } else {
            $this->addButtonRow(InlineKeyboardButton::make("\xF0\x9F\x94\x99", callback_data: 'categories'));
        }

        $this->menuText($text, ['parse_mode' => ParseMode::HTML])->showMenu();
    }

    public function handlePrev(Nutgram $bot)
    {
        $this->clearButtons();
        $data = $this->getDataFromString($bot->callbackQuery()->data);
        $data['curPage']--;
        Log::debug("Prev" . $data['curPage']);

        if ($data['category']) {
            $product = $this->mageRepository->getProducts($data['category'], 1, $data['curPage'])->last();
        } elseif ($data['keyword']) {
            $product = $this->mageRepository->getProductsLike('name', $data['keyword'], 1, $data['curPage'])->last();
        }

        $text = 'Больше товаров нет!';

        if (isset($product)) {
            $this->addButtonRow(InlineKeyboardButton::make("Закрепить", callback_data: "category=".$data['category'].",curPage=".$data['curPage'].",keyword=".$data['keyword']."@handlePin"));
            if (isset($product->media_gallery_entries[0]->file)) {
                $this->bot->setData('photo', config('global.magento_url')."/pub/media/catalog/product".$product->media_gallery_entries[0]->file);
            }
            $this->bot->setData('sku', $product->sku);
            $this->bot->setData('keyword', $data['keyword']);
            $text = "<b>$product->name</b>\n"
                . "Art.: $product->sku\n"
                . "Цена: $product->price" . config('global.currency') . "\n"
                . "Ссылка: " . config('global.magento_url') . "/catalog/product/view/id/" . $product->id;
        }

        if ($data['curPage'] > 1) {
            $this->addButtonRow(
                InlineKeyboardButton::make("Назад", callback_data: "category=".$data['category'].",curPage=".$data['curPage'].",action=prev,keyword=".$data['keyword']."@handleButton"),
                InlineKeyboardButton::make("Дальше", callback_data: "category=".$data['category'].",curPage=".$data['curPage'].",action=next,keyword=".$data['keyword']."@handleButton")
            );
        } else {
            $this->addButtonRow(
                InlineKeyboardButton::make("Дальше", callback_data: "category=".$data['category'].",curPage=".$data['curPage'].",action=next,keyword=".$data['keyword']."@handleButton")
            );
        }

        if (isset($data['keyword'])) {
            $this->addButtonRow(InlineKeyboardButton::make("\xF0\x9F\x94\x99", callback_data: 'start_search_menu'));
        } else {
            $this->addButtonRow(InlineKeyboardButton::make("\xF0\x9F\x94\x99", callback_data: 'categories'));
        }

        $this->menuText($text, ['parse_mode' => ParseMode::HTML])->showMenu();
    }

    public function handlePin(Nutgram $bot)
    {
        $data = $this->getDataFromString($bot->callbackQuery()->data);

        if ($data['category']) {
            $product = $this->mageRepository->getProducts($data['category'], 1, $data['curPage'])->last();
        } elseif ($data['keyword']) {
            $product = $this->mageRepository->getProductsLike('name', $data['keyword'], 1, $data['curPage'])->last();
        }

        if ($product) {
            if (isset($product->media_gallery_entries[0]->file)) {
                $photo = config('global.magento_url')."/pub/media/catalog/product".$product->media_gallery_entries[0]->file;
            }
            $this->bot->setData('sku', $product->sku);
            $text = "<b>$product->name</b>\n"
                . "Art.: $product->sku\n"
                . "Цена: $product->price" . config('global.currency') . "\n"
                . "Ссылка: " . config('global.magento_url') . "/catalog/product/view/id/" . $product->id;
            if (isset($photo)) {
                $url = $photo;
                $path = public_path() . '/img/' . $this->bot->getData('sku') . '.jpg';
                $img = $path;
                file_put_contents($img, file_get_contents($url));
                $photo = fopen($path, 'r+');
                $this->bot->sendPhoto($photo, array_merge([
                    'caption' => $text,
                    'parse_mode' => ParseMode::HTML
                ]));
            } else {
                $this->bot->sendMessage($text);
            }
        }
        $bot->setData('curPage', $data['curPage']);
        $this->handleNext($bot);
    }

    private function getDataFromString(string $str): array
    {
        $items = explode(",", $str);
        $data = array_map(function($item) {
            $parts = explode("=", trim($item));
            return [$parts[0] => $parts[1]];
        }, $items);
        return array_merge(...$data);
    }

    protected function doOpen(string $text, InlineKeyboardMarkup $buttons, array $opt): Message|null
    {
        if (!is_null($photo = $this->bot->getData('photo'))) {
            $url = $photo;
            $path = public_path() . '/img/' . $this->bot->getData('sku') . '.jpg';
            Log::debug($path);
            $img = $path;
            file_put_contents($img, file_get_contents($url));

            $photo = fopen($path, 'r+');
            $message = $this->bot->sendPhoto($photo, array_merge([
                'caption' => $text,
                'reply_markup' => $buttons,
            ], $opt));
        } else {
            $message = $this->bot->sendMessage($text, array_merge([
                'reply_markup' => $buttons,
            ], $opt));
        }

        if (is_array($message)) {
            throw new RuntimeException('Multiple messages are not supported by the InlineMenu class. Please provide a shorter text.');
        }

        return $message;
    }

    protected function doUpdate(
        string $text,
        ?int $chatId,
        ?int $messageId,
        InlineKeyboardMarkup $buttons,
        array $opt
    ): bool|Message|null {
        $this->bot->deleteMessage($chatId, $messageId);
        if (!is_null($photo = $this->bot->getData('photo'))) {
            $url = $photo;
            $path = public_path('img') . $this->bot->getData('sku') . '.jpg';
            $img = $path;
            file_put_contents($img, file_get_contents($url));
            $photo = fopen($path, 'r+');
            return $this->bot->sendPhoto($photo, array_merge([
                'caption' => $text,
                'reply_markup' => $buttons,
            ], $opt));
        }
        return $this->bot->sendMessage($text, array_merge([
            'reply_markup' => $buttons,
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ], $opt));
    }
}
