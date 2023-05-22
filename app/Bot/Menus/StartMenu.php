<?php

namespace App\Bot\Menus;

use App\Magento\Config\MageConfig;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

class StartMenu extends AbstractMenu
{
    const LOGIN = 'login';
    const HELP = 'help';

    public int $id;
    public User $user;

    public function __construct(protected MageConfig $mageConfig)
    {
        parent::__construct();
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
            ->addButtonRow(InlineKeyboardButton::make('Популярные товары', callback_data: 'start_bestseller_products'))
            ->addButtonRow(InlineKeyboardButton::make('Техническая поддержка', callback_data: '@handleHelp'))
            ->addButtonRow(InlineKeyboardButton::make('Поиск товаров', callback_data: 'start_search_menu'))
            ->addButtonRow(InlineKeyboardButton::make('Акции', callback_data: 'start_sales_menu'))
            ->orNext('none');
        if (User::checkAuth($chatId)) {
            $this->addButtonRow(InlineKeyboardButton::make('Мои заказы', callback_data: 'start_orders_menu'));
        }
        $this->showMenu();
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

    /**
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function handleHelp(Nutgram $bot)
    {
        $this->clearButtons();
        $this->menuText("Загрузка...")->showMenu();
        $username = $bot->user()->username;
        if (!is_null($username)) {
            $this->mageConfig->sendInviteLink("https://t.me/$username");
            $supportUrl = "https://t.me/" . $this->mageConfig->getSupportUsername();
            Log::debug("Link Sended");
            $this->addButtonRow(InlineKeyboardButton::make('Перейти в чат', url: $supportUrl));
            $this->menuText("Запрос успешно отправлен. Пожалуйста, перейдите в чат и ожидайте, скоро с вами свяжется оператор")->showMenu();
        } else {
            Log::debug("Link Not Sended");
            $this->addButtonRow(InlineKeyboardButton::make('Вернуться', callback_data: '@start'));
            $this->menuText('В связи с ограничениями Telegram мы не можем направить запрос на техническую поддержку.\n'
                . 'Пожалуйста, укажите в профиле "Имя пользователя/Username", чтобы мы могли с Вам связаться')->showMenu();
        }
    }

    public function none(Nutgram $bot)
    {
        $this->end();
    }
}


