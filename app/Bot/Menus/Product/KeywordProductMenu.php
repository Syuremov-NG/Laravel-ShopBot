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

class KeywordProductMenu extends AbstractProductMenu
{
    /**
     * @param Nutgram $bot
     * @param string $action
     * @return Closure|mixed|null
     * @throws InvalidArgumentException
     */
    public function getProduct(Nutgram $bot, string $action = ''): mixed
    {
        $data = $bot->getGlobalData($bot->chatId());
        if ($data['keyword']) {
            $product = $this->mageRepository->getProductsLike('name', $data['keyword'], 1, $data['cur_page'])->last();
        }
        $bot->setGlobalData($bot->chatId(), $data);
        return $product ?? null;
    }
}
