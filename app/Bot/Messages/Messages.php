<?php

namespace App\Bot\Messages;

use App\Models\User;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

class Messages
{
    public static function getNotLoggedMessage(Nutgram $bot): void
    {
        $webAppLogin = new WebAppInfo(config('global.url') . "/login?id=" . $bot->chatId());
        $user = User::firstOrCreate([User::NAME => $bot->user()->username, User::TELEGRAM_ID => $bot->chatId()]);
        $message = $bot->sendMessage("Please Sign In.", [
            'reply_markup' => InlineKeyboardMarkup::make()->addRow(
                InlineKeyboardButton::make('Log In!', web_app: $webAppLogin)
            )
        ]);
        $user->last_message = $message->message_id;
        $user->save();
    }
}
