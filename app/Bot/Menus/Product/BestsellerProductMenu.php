<?php

namespace App\Bot\Menus\Product;

use Closure;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;

class BestsellerProductMenu extends AbstractProductMenu
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
        $product = $this->mageRepository->getBestsellerProducts($data['cur_page'])->last();
        return $product ?? null;
    }
}
