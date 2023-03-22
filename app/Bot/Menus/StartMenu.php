<?php

namespace App\Bot\Menus;

use App\Models\User;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Conversations\InlineMenu;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

class StartMenu extends InlineMenu
{
    const LOGIN = 'login';
    const HELP = 'help';
    const SEARCH = 'search';
    const NEWS = 'news';
    const PROMO = 'promo';

    public int $id;
    public User $user;

    public function start(Nutgram $bot)
    {
        $this->clearButtons();
        $chatId = $bot->chatId() ?? $this->chatId;
        $this->user = User::firstOrCreate([User::TELEGRAM_ID => $chatId]);
        if (!User::checkAuth($chatId)) {
            $this->addButtonRow(InlineKeyboardButton::make('Авторизация', callback_data: self::LOGIN . '@handleLogin'));
        }
        $this->menuText("Добро пожаловать в M2-Shop телеграм бота.\nНа данный момент Вам доступны следующие функции:")
            ->addButtonRow(InlineKeyboardButton::make('Техническая поддержка', callback_data: self::HELP . '@handleHelp'))
            ->addButtonRow(InlineKeyboardButton::make('Поиск товаров', callback_data: self::SEARCH . '@handleSearch'))
            ->addButtonRow(InlineKeyboardButton::make('Новости', callback_data: self::NEWS . '@handleNews'))
            ->addButtonRow(InlineKeyboardButton::make('Акции', callback_data: self::PROMO . '@handlePromo'))
            ->orNext('none')
            ->showMenu();
    }

    public function handleLogin(Nutgram $bot) {
        $this->user->last_message = $this->messageId;
        $this->user->save();
        $this->clearButtons();
        $webAppLogin = new WebAppInfo(
            config('global.url') . "/login?id=" . $this->chatId . "&message=" . $this->messageId
        );
        $this->addButtonRow(InlineKeyboardButton::make('Войти', web_app: $webAppLogin));
        $this->addButtonRow(InlineKeyboardButton::make("\xF0\x9F\x94\x99", callback_data: '@start'));
        $this->menuText("Для авторизации нажмите на кнопку ниже.")->showMenu();
    }

    public function handleHelp(Nutgram $bot) {
        $this->clearButtons();
    }

    public function handleSearch(Nutgram $bot) {
        $this->clearButtons();
    }

    public function handleNews(Nutgram $bot) {
        $this->clearButtons();
    }

    public function handlePromo(Nutgram $bot) {
        $this->clearButtons();
    }

    public function none(Nutgram $bot)
    {
        $this->closeMenu('Bye!');
        $this->end();
        // TODO: Сделать выход из меню в виде логаута. При следующем входе в меню, пользователю надо опять регаться.
    }
}


