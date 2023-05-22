<?php

namespace App\Bot\CallbackHandler\ProductMenu;

use App\Bot\Api\MenuHandlerInterface;
use App\Bot\Menus\Product\BestsellerProductMenu;
use App\Bot\Menus\Product\CategoryProductMenu;
use App\Models\AnalyticMenus;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

class BestsellerProductMenuHandler implements MenuHandlerInterface
{
    const NAME = 'bestsellers';

    public function __construct(protected BestsellerProductMenu $startMenu)
    {
    }

    static public function execute(Nutgram $bot, string $key = '', mixed $value = '', string $return = 'start'): void
    {
        $bot->setData('return', $return);
        self::analytic();
        Log::debug("start bestseller menu");
        BestsellerProductMenu::begin($bot);
    }

    static public function analytic(): void
    {
        $menu = AnalyticMenus::firstOrCreate([AnalyticMenus::NAME => self::NAME]);
        $menu->clicks = $menu->clicks + 1;
        $menu->save();
    }
}
