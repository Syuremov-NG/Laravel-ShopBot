<?php

namespace App\Bot\CallbackHandler;

use App\Bot\Menus\SalesMenu;
use App\Models\AnalyticMenus;
use SergiX44\Nutgram\Nutgram;

class SalesMenuHandler
{
    const NAME = 'sales';

    public function __construct(protected SalesMenu $salesMenu)
    {
    }

    static public function execute(Nutgram $bot, string $key = '', string $value = ''): void
    {
        if ($key) {
            $bot->setData($key, $value);
        }
        self::analytic();
        SalesMenu::begin($bot);
    }

    static private function analytic(): void
    {
        $menu = AnalyticMenus::firstOrCreate([AnalyticMenus::NAME => self::NAME]);
        $menu->clicks = $menu->clicks + 1;
        $menu->save();
    }
}
