<?php

namespace App\Bot\CallbackHandler;

use App\Bot\Menus\StartMenu;
use App\Models\AnalyticMenus;
use App\Models\User;
use Exception;
use SergiX44\Nutgram\Nutgram;

class StartMenuHandler
{
    const NAME = 'start';

    public function __construct(protected StartMenu $startMenu)
    {
    }

    public function execute(Nutgram $bot, string $param = '')
    {
        $messageId = User::where(User::TELEGRAM_ID, $bot->chatId())->first()?->last_message;
        try {
            if ($messageId) {
                $bot->deleteMessage($bot->chatId(), $messageId);
            }
        } catch (Exception $ignore) {
        }
        $this->analytic();
        StartMenu::begin($bot);
    }

    public function analytic()
    {
        $menu = AnalyticMenus::firstOrCreate([AnalyticMenus::NAME => self::NAME]);
        $menu->clicks = $menu->clicks + 1;
        $menu->save();
    }
}
