<?php

namespace App\Bot\Menus;

use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Conversations\InlineMenu;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class Test extends InlineMenu {

    protected ?string $step = 'askCupSize';

    public $cupSize;

    public function askCupSize(Nutgram $bot)
    {
        $bot->sendMessage('How big should be you ice cream cup?', [
            'reply_markup' => InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('Small', callback_data: 'S'), InlineKeyboardButton::make('Medium', callback_data: 'M'))
                ->addRow(InlineKeyboardButton::make('Big', callback_data: 'L'), InlineKeyboardButton::make('Super Big', callback_data: 'XL')),
        ]);
        $this->next('askFlavors');
    }

    public function askFlavors(Nutgram $bot)
    {
        // if is not a callback query, ask again!
        if (!$bot->isCallbackQuery()) {
            $this->askCupSize($bot);
            return;
        }

        $this->cupSize = $bot->callbackQuery()->data;

        $this->menuText('Напишите полное или частичное название товара.')->showMenu();
        $this->next('recap');
    }

    public function recap(Nutgram $bot)
    {
        $flavors = $bot->message()->text;

        $bot->sendMessage("You want an $this->cupSize cup with this flavors: $flavors");
        $this->end();
    }
}
