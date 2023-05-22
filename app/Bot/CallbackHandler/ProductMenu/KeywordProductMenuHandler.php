<?php

namespace App\Bot\CallbackHandler\ProductMenu;

use App\Bot\Api\MenuHandlerInterface;
use App\Bot\Menus\Product\KeywordProductMenu;
use App\Models\AnalyticMenus;
use SergiX44\Nutgram\Nutgram;

class KeywordProductMenuHandler implements MenuHandlerInterface
{
    const NAME = 'keyword_product_search';

    public function __construct(protected KeywordProductMenu $startMenu)
    {
    }

    static public function execute(Nutgram $bot, string $key = '', mixed $value = '', string $return = ''): void
    {
        if ($key) {
            $bot->setData($key, $value);
        }
        if ($return) {
            $bot->setData('return', $return);
        }
        self::analytic();
        KeywordProductMenu::begin($bot);
    }

    static public function analytic(): void
    {
        $menu = AnalyticMenus::firstOrCreate([AnalyticMenus::NAME => self::NAME]);
        $menu->clicks = $menu->clicks + 1;
        $menu->save();
    }
}
