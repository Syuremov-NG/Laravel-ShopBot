<?php

namespace App\Commands;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Nutgram;

class StartCommand extends Command
{
    protected $name = 'start';

    public function handle(Nutgram $bot): void
    {
        // Получаем текущего пользователя
        /** @var User $user */
        $user = Auth::user();

        // Отправляем пользователю приветственное сообщение и инструкции для входа в систему
        $bot->sendMessage('Добро пожаловать! Для входа в систему перейдите по ссылке /login?telegram_id=' . $user->telegram_id);
    }
}
