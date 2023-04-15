<?php

namespace App\Bot\Menus;

use App\Magento\Repository\MageRepository;
use App\Models\User;
use SergiX44\Nutgram\Conversations\InlineMenu;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Attributes\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

class OrdersMenu extends InlineMenu
{
    public function __construct(protected MageRepository $mageRepository)
    {
    }

    public function start(Nutgram $bot)
    {
        $this->clearButtons();
        $chatId = $bot->chatId() ?? $this->chatId;
        if (!User::checkAuth($chatId)) {
            $this->menuText("К сожалению, время сеанса окончено.\nЧтобы просмотреть этот раздел, пожалуйста, авторизируйтесь.")
                ->addButtonRow(InlineKeyboardButton::make("\xF0\x9F\x94\x99", callback_data: 'start'))
                ->showMenu();
            return;
        }
        $user = User::where(User::TELEGRAM_ID, $chatId)->first();

        if (!$user->notify) {
            $this->addButtonRow(InlineKeyboardButton::make("Включить оповещения", callback_data: "$chatId@changeNotify"));
        } else {
            $this->addButtonRow(InlineKeyboardButton::make("Отключить оповещения", callback_data: "$chatId@changeNotify"));
        }

        $orders = $this->mageRepository->getOrders($chatId);
        $textArr = [];
        foreach ($orders as $order) {
            $textArr[] = "Заказ № ".$order->increment_id." - Статус <b>".$order->status . "</b>\n"
                . "Подробнее: " . config('global.magento_url') . '/sales/order/view/order_id/' . $order->entity_id . "\n"
            . "═══════════════════════════\n";
        }
        $text = 'У вас нет заказов. Самое время совершать покупки!';
        if (!empty($textArr)) {
            $text = implode('', $textArr);
        }
        $this->menuText($text, ['parse_mode' => ParseMode::HTML]);
        $this->addButtonRow(InlineKeyboardButton::make("\xF0\x9F\x94\x99", callback_data: 'start'))
            ->showMenu();
    }

    public function changeNotify(Nutgram $bot) {
        $chatId = $bot->callbackQuery()->data;
        $user = User::where(User::TELEGRAM_ID, $chatId)->first();
        $user->notify = $user->notify == 0 ? 1 : 0;
        $user->save();
        $this->start($bot);
    }

    public function none(Nutgram $bot)
    {
        $this->end();
    }
}
