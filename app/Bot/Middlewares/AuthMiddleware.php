<?php

namespace App\Bot\Middlewares;

use App\Bot\Messages\Messages;
use App\Models\User;
use Carbon\Carbon;
use SergiX44\Nutgram\Nutgram;

class AuthMiddleware
{
    public function __invoke(Nutgram $bot, $next)
    {
        $user = User::where(User::TELEGRAM_ID, $bot->chatId())->first();
        $currentDate = Carbon::now();

        if ($user
            && $currentDate->diffInSeconds(Carbon::parse($user->token_updated)) < config('global.token_lifetime')
            && $user->token
        ) {
            $next($bot);
            return;
        }

        if ($user && $user->token) {
            $bot->sendMessage('The session has expired.');
        }

        Messages::getNotLoggedMessage($bot);
    }
}
