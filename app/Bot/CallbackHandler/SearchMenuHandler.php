<?php

namespace App\Bot\CallbackHandler;

use App\Bot\Menus\SearchMenu;
use App\Models\AnalyticMenus;
use App\Models\User;
use Exception;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;

class SearchMenuHandler
{
    const NAME = 'search';

    public function __construct(protected SearchMenu $startMenu)
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
        SearchMenu::begin($bot);
    }

    public function executeCategories(Nutgram $bot)
    {
        try {
            $bot->deleteGlobalData($bot->chatId());
        } catch (InvalidArgumentException $e) {
        }
        $this->analytic();
        SearchMenu::trigger($bot, 'handleManual');
    }

    public function analytic()
    {
        $menu = AnalyticMenus::firstOrCreate([AnalyticMenus::NAME => self::NAME]);
        $menu->clicks = $menu->clicks + 1;
        $menu->save();
    }
}
