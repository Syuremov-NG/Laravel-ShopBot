<?php

namespace App\Bot\Menus\Product;

use App\Bot\Menus\AbstractMenu;
use App\Magento\Repository\MageRepository;
use Closure;
use Illuminate\Support\Facades\File;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Attributes\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Message\Message;

class NeuroProductMenu extends AbstractProductMenu
{
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
            case 'similar':
                $this->handleSimilar($bot);
                break;
        }
    }

    public function handleSimilar(Nutgram $bot)
    {
        $this->clearButtons();
        $data = $bot->getGlobalData($bot->chatId());
        $data['cur_page'] = 1;
        $data['similar'] = $data['neuro_label'][$data['neuro_page']];
        $data['neuro_label'] = [];
        $bot->setGlobalData($bot->chatId(), $data);

        $product = $this->getProduct($bot, 'similar');
        $text = 'Больше товаров нет!';

        if (isset($product)) {
            $this->addPinButton();
            $text = $this->fillProductDataMessage($product);
        }

        $this->addNextButton();

        $this->getReturnButton($bot);
        $this->menuText($text, ['parse_mode' => ParseMode::HTML])->showMenu();
    }

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
     * @param Nutgram $bot
     * @param string $action
     * @return Closure|mixed|null
     * @throws InvalidArgumentException
     */
    public function getProduct(Nutgram $bot, string $action = ''): mixed
    {
        $data = $bot->getGlobalData($bot->chatId());
        if (isset($data['similar'])) {
            $product = $this->mageRepository->getProductsByType($data['similar'], 1, $data['cur_page'])->last();
        } else if ($neuroLabels = $data['neuro_label'] ?? []) {
            $product = null;
            $curPage = $data['neuro_page'];
            if ($action == 'prev') {
                while (!$product && $curPage > 0) {
                    $curPage--;
                    $product = $this->mageRepository->getProductsByType($neuroLabels[$curPage] ?? 'null', 1, 1)->last();
                }
            } elseif ($action == '' || $action == 'next') {
                while (!$product && ($curPage < count($neuroLabels))) {
                    $curPage++;
                    $product = $this->mageRepository->getProductsByType($neuroLabels[$curPage] ?? 'null', 1, 1)->last();
                }
            }
            $data['neuro_page'] = $curPage;
        }
        $bot->setGlobalData($bot->chatId(), $data);
        return $product ?? null;
    }
}
