<?php

namespace App\Bot\CallbackHandler\ProductMenu;

use App\Bot\Api\MenuHandlerInterface;
use App\Bot\Menus\Product\CategoryProductMenu;
use App\Models\AnalyticMenus;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

class CategoryProductMenuHandler implements MenuHandlerInterface
{
    const NAME = 'category_product_search';

    public function __construct(protected CategoryProductMenu $startMenu)
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
        Log::debug("start category menu");
        CategoryProductMenu::begin($bot);
    }

    static public function analytic(): void
    {
        $menu = AnalyticMenus::firstOrCreate([AnalyticMenus::NAME => self::NAME]);
        $menu->clicks = $menu->clicks + 1;
        $menu->save();
    }
}
