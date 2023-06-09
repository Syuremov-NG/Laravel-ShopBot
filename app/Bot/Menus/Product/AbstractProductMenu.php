<?php

namespace App\Bot\Menus\Product;

use App\Bot\Menus\AbstractMenu;
use App\Magento\Repository\MageRepository;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Attributes\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Message\Message;

abstract class AbstractProductMenu extends AbstractMenu
{
    public function __construct(protected MageRepository $mageRepository)
    {
        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function start(Nutgram $bot)
    {
        $this->clearButtons();

        $data = [
            'category' => $bot->getData('category'),
            'keyword' => $bot->getData('keyword') ?? '',
            'neuro_label' => $bot->getData('neuro_label') ?? '',
            'cur_page' => 1,
            'neuro_page' => -1,
            'return' => $bot->getData('return') ?? '',
            'sku_list' => $bot->getData('sku_list') ?? []
        ];

        $this->bot->setGlobalData($bot->chatId(), $data);

        $product = $this->getProduct($bot);

        $text = 'К сожалению, товаров не нашлось.';

        if ($product) {
            $this->addPinButton();
            $text = $this->fillProductDataMessage($product);
            $this->addNextButton();
        }

        $this->getReturnButton($bot);

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
            case 'pin':
                $this->handlePin($bot);
                break;
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handleNext(Nutgram $bot)
    {
        $this->clearButtons();
        $data = $bot->getGlobalData($bot->chatId());
        $data['cur_page']++;
        $bot->setGlobalData($bot->chatId(), $data);

        $product = $this->getProduct($bot, 'next');
        $text = 'Больше товаров нет!';
        $ans = false;

        if ($product) {
            $this->addPinButton();
            $text = $this->fillProductDataMessage($product);
            $ans = true;
        }
        if ($ans) {
            $this->addBackNextButtons();
        } else {
            $this->addBackButton();
        }

        $this->getReturnButton($bot);
        $this->menuText($text, ['parse_mode' => ParseMode::HTML])->showMenu();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handlePrev(Nutgram $bot)
    {
        $this->clearButtons();
        $data = $bot->getGlobalData($bot->chatId());
        $data['cur_page']--;
        $bot->setGlobalData($bot->chatId(), $data);

        $product = $this->getProduct($bot, 'prev');
        $text = 'Больше товаров нет!';

        if (isset($product)) {
            $this->addPinButton();
            $text = $this->fillProductDataMessage($product);
        }

        if ($data['cur_page'] > 1) {
            $this->addBackNextButtons();
        } else {
            $this->addNextButton();
        }

        $this->getReturnButton($bot);
        $this->menuText($text, ['parse_mode' => ParseMode::HTML])->showMenu();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handlePin(Nutgram $bot)
    {
        $product = $this->getProduct($bot, 'pin');
        $data = $bot->getGlobalData($bot->chatId());
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
                File::delete($path);
            } else {
                $this->bot->sendMessage($text);
            }
        }
        $text = 'Больше товаров нет!';
        if ($product) {
            $text = $this->fillProductDataMessage($product);
        }
        $this->menuText($text, ['parse_mode' => ParseMode::HTML])->showMenu();
    }

    protected function getDataFromString(string $str): array
    {
        $items = explode(",", $str);
        $data = array_map(function($item) {
            $parts = explode("=", trim($item));
            return [$parts[0] => $parts[1]];
        }, $items);
        return array_merge(...$data);
    }

    public function getReturnButton(Nutgram $bot)
    {
        $data = $this->bot->getGlobalData($bot->chatId());
        $this->addButtonRow(InlineKeyboardButton::make("\xF0\x9F\x94\x99", callback_data: $data['return'] ?? 'start'));
    }

    /**
     * @param mixed $product
     * @return string
     */
    public function fillProductDataMessage(mixed $product): string
    {
        if (isset($product->media_gallery_entries[0]->file)) {
            $photo = config('global.magento_url')."/pub/media/catalog/product".$product->media_gallery_entries[0]->file;
            $this->bot->setData('photo', $photo);
        }
        return "<b>$product->name</b>\n"
            . "Art.: $product->sku\n"
            . "Цена: $product->price" . config('global.currency') . "\n"
            . "Ссылка: " . config('global.magento_url') . "/catalog/product/view/id/" . $product->id;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function addPinButton()
    {
        $data = $this->bot->getGlobalData($this->bot->chatId());
        if (!$data['neuro_label'] ?? '') {
            $this->addButtonRow(InlineKeyboardButton::make(
                "Закрепить",
                callback_data: "action=pin@handleButton"
            ));
        } else {
            $this->addButtonRow(InlineKeyboardButton::make(
                "Показать похожие",
                callback_data: "action=similar@handleButton"
            ));
        }
    }

    /**
     * @return void
     */
    public function addBackNextButtons(): void
    {
        $this->addButtonRow(
            InlineKeyboardButton::make("Назад",
                callback_data: "action=prev@handleButton"),
            InlineKeyboardButton::make("Дальше",
                callback_data: "action=next@handleButton")
        );
    }

    public function addBackButton(): void
    {
        $this->addButtonRow(
            InlineKeyboardButton::make(
                "Назад",
                callback_data: "action=prev@handleButton"
            )
        );
    }

    public function addNextButton(): void
    {
        $this->addButtonRow(
            InlineKeyboardButton::make(
                "Дальше",
                callback_data: "action=next@handleButton"
            )
        );
    }

    protected function doOpen(string $text, InlineKeyboardMarkup $buttons, array $opt): Message|null
    {
        if (!is_null($photo = $this->bot->getData('photo'))) {
            $url = $photo;
            $path = public_path() . '/img/' . $this->bot->getData('sku') . '.jpg';
            $img = $path;
            file_put_contents($img, file_get_contents($url));

            $photo = fopen($path, 'r+');
            Log::debug('Text doOpen '  . $text);
            Log::debug('Buttons ' . json_encode($buttons->jsonSerialize()));
            $message = $this->bot->sendPhoto($photo, array_merge([
                'caption' => $text,
                'reply_markup' => $buttons,
            ], $opt));
            File::delete($path);
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
            $message = $this->bot->sendPhoto($photo, array_merge([
                'caption' => $text,
                'reply_markup' => $buttons,
            ], $opt));
            File::delete($path);
            return $message;
        }
        return $this->bot->sendMessage($text, array_merge([
            'reply_markup' => $buttons,
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ], $opt));
    }

    abstract function getProduct(Nutgram $bot, string $action = '');
}
