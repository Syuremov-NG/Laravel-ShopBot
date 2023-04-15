<?php

namespace App\Bot\Menus;

use App\Models\User;
use InvalidArgumentException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Conversations\InlineMenu;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

class StartMenu extends AbstractMenu
{
    const LOGIN = 'login';
    const HELP = 'help';
    const SEARCH = 'search';
    const NEWS = 'news';
    const PROMO = 'promo';

    public int $id;
    public User $user;

    public function __construct(
        protected SearchMenu $searchMenu,
        protected OrdersMenu $ordersMenu
    ) {
    }

    public function start(Nutgram $bot)
    {
        $this->clearButtons();
        $chatId = $bot->chatId() ?? $this->chatId;
        $this->user = User::firstOrCreate([User::TELEGRAM_ID => $chatId]);
        try {
            $bot->deleteGlobalData($bot->chatId());
        } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
        }
        if (!User::checkAuth($chatId)) {
            $this->addButtonRow(InlineKeyboardButton::make('Авторизация', callback_data: self::LOGIN . '@handleLogin'));
        }
        $this->menuText("Добро пожаловать в M2-Shop телеграм бота.\nНа данный момент Вам доступны следующие функции:")
            ->addButtonRow(InlineKeyboardButton::make('Техническая поддержка', callback_data: self::HELP . '@handleHelp'))
            ->addButtonRow(InlineKeyboardButton::make('Поиск товаров', callback_data: 'start_search_menu'))
            ->addButtonRow(InlineKeyboardButton::make('Акции', callback_data: self::PROMO . '@handlePromo'))
            ->orNext('none');
        if (User::checkAuth($chatId)) {
            $this->addButtonRow(InlineKeyboardButton::make('Мои заказы', callback_data: 'start_orders_menu'));
        }
        $this->showMenu();
    }

    public function handleOrders(Nutgram $bot)
    {
        $this->clearButtons();
        $this->ordersMenu->start($bot);
    }

    public function handleLogin(Nutgram $bot)
    {
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

    public function handleHelp(Nutgram $bot)
    {
        $this->closeMenu();
        $this->ordersMenu->start($bot);
    }

    public function handleSearch(Nutgram $bot)
    {
        $this->closeMenu();
        $this->searchMenu->start($bot);
    }

    public function handlePromo(Nutgram $bot)
    {
        $this->clearButtons();
    }

    public function none(Nutgram $bot)
    {
        $this->end();
    }
}


