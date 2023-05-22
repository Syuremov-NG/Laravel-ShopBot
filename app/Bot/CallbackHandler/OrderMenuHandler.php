<?php

namespace App\Bot\CallbackHandler;

use App\Bot\Menus\OrdersMenu;
use App\Models\AnalyticMenus;
use App\Models\User;
use Exception;
use SergiX44\Nutgram\Nutgram;

class OrderMenuHandler
{
    const NAME = 'order';

    public function __construct(protected OrdersMenu $startMenu)
    {
    }

    public function execute(Nutgram $bot, string $param = '')
    {
        $messageId = User::where(User::TELEGRAM_ID, $bot->chatId())->first()?->last_message;
        try {
            $bot->deleteMessage($bot->chatId(), $messageId);
        } catch (Exception $ignore) {
        }
        $this->analytic();
        OrdersMenu::begin($bot);
    }

    public function analytic()
    {
        $menu = AnalyticMenus::firstOrCreate([AnalyticMenus::NAME => self::NAME]);
        $menu->clicks = $menu->clicks + 1;
        $menu->save();
    }
}
