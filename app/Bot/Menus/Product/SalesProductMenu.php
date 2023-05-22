<?php

namespace App\Bot\Menus\Product;

use App\Bot\Menus\AbstractMenu;
use App\Magento\Repository\MageRepository;
use Closure;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Attributes\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Message\Message;

class SalesProductMenu extends AbstractProductMenu
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
        $product = null;
        $skuList = $data['sku_list'] ?? [];
        $sku = $skuList[$data['cur_page'] - 1] ?? '';
        Log::debug('sku page ' . $data['cur_page'] - 1);
        if ($skuList && $sku) {
            $product = $this->mageRepository->getProductBySku($skuList[$data['cur_page'] - 1]);
        }
        return $product ?? null;
    }
}
