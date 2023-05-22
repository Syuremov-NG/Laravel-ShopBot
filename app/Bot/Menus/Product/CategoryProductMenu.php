<?php

namespace App\Bot\Menus\Product;

use Closure;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;

class CategoryProductMenu extends AbstractProductMenu
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
        if ($data['category']) {
            $product = $this->mageRepository->getProducts($data['category'], 1, $data['cur_page'])->last();
        }
        $bot->setGlobalData($bot->chatId(), $data);
        return $product ?? null;
    }
}
