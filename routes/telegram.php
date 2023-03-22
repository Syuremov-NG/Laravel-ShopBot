<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Bot\Menus\StartMenu;
use App\Bot\Middlewares\AuthMiddleware;
use App\Magento\Repository\MageRepository;
use App\Models\User;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

// Authentication
//$bot->middleware(AuthMiddleware::class);

//Main Loop
$bot->onCommand('start', StartMenu::class)->description('Start');

//Remove Keyboard
$bot->onCommand('cancel', function (Nutgram $bot) {
    $bot->sendMessage('Removing keyboard...', [
        'reply_markup' => ReplyKeyboardRemove::make(true),
    ])?->delete();
})->description('Remove keyboard buttons.');

$bot->onCallbackQueryData('auth_success|auth_failed', function (Nutgram $bot) {
    $messageId = User::where(User::TELEGRAM_ID, $bot->chatId())->first()?->last_message;
    try {
        $bot->deleteMessage($bot->chatId(), $messageId);
    } catch (Exception $ignore) {
    }
    StartMenu::begin($bot);
});

$bot->registerMyCommands();

//Test
$bot->onCommand('products', function (Nutgram $bot) {
    $repo = new MageRepository();
    $bot->sendMessage(substr($repo->getProducts($bot->chatId()), 0, 100));
});
