<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Bot\Menus\ProductMenu;
use App\Bot\Menus\SearchMenu;
use App\Bot\Menus\StartMenu;
use App\Bot\Middlewares\AuthMiddleware;
use App\Magento\Repository\MageRepository;
use App\Models\User;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

// Authentication
//$bot->middleware(AuthMiddleware::class);

//Remove Keyboard
$bot->onCommand('cancel', function (Nutgram $bot) {
    $bot->sendMessage('Removing keyboard...', [
        'reply_markup' => ReplyKeyboardRemove::make(true),
    ])?->delete();
})->description('Remove keyboard buttons.');

// Menus
$bot->onCommand('start', StartMenu::class)->description('Start');

$bot->onCallbackQueryData('auth_success|auth_failed|start', function (Nutgram $bot) {
    $messageId = User::where(User::TELEGRAM_ID, $bot->chatId())->first()?->last_message;
    try {
        $bot->deleteMessage($bot->chatId(), $messageId);
    } catch (Exception $ignore) {
    }
    StartMenu::begin($bot);
});

$bot->onCallbackQueryData('start_search_menu', SearchMenu::class);

$bot->onCallbackQueryData('categories', function (Nutgram $bot) {
    SearchMenu::trigger($bot, 'handleManual');
});

$bot->onCallbackQueryData('show_products {param}', function (Nutgram $bot, $param) {
    $bot->setData('category_id', $param);
    ProductMenu::begin($bot);
});

$bot->onCallbackQueryData('keyword {param}', function (Nutgram $bot, $param) {
    $bot->setData('keyword', $param);
    ProductMenu::begin($bot);
});
// Menus

//Test
$bot->onCommand('test', function (Nutgram $bot) {
    \App\Bot\Menus\Test::begin($bot);
});
