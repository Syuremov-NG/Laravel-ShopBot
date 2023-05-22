<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Bot\CallbackHandler\OrderMenuHandler;
use App\Bot\CallbackHandler\ProductMenu\BestsellerProductMenuHandler;
use App\Bot\CallbackHandler\ProductMenu\CategoryProductMenuHandler;
use App\Bot\CallbackHandler\ProductMenu\KeywordProductMenuHandler;
use App\Bot\CallbackHandler\ProductMenu\NeuroProductMenuHandler;
use App\Bot\CallbackHandler\SalesMenuHandler;
use App\Bot\CallbackHandler\SearchMenuHandler;
use App\Bot\CallbackHandler\StartMenuHandler;
use App\Bot\Menus\Product\BestsellerProductMenu;
use App\Bot\Menus\SearchMenu;
use danog\MadelineProto\API;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

//Remove Keyboard
$bot->onCommand('cancel', function (Nutgram $bot) {
    $bot->sendMessage('Removing keyboard...', [
        'reply_markup' => ReplyKeyboardRemove::make(true),
    ])?->delete();
})->description('Remove keyboard buttons.');

// Menus
$bot->onCommand('start', [StartMenuHandler::class, 'execute'])->description('Start');

$bot->onCallbackQueryData('auth_success|auth_failed|start', [StartMenuHandler::class, 'execute']);

$bot->onCallbackQueryData('start_search_menu', [SearchMenuHandler::class, 'execute']);

$bot->onCallbackQueryData('start_orders_menu', [OrderMenuHandler::class, 'execute']);

$bot->onCallbackQueryData('start_sales_menu', [SalesMenuHandler::class, 'execute']);

$bot->onCallbackQueryData('categories', [SearchMenuHandler::class, 'executeCategories']);

$bot->onCallbackQueryData('show_category_products {key}-{value},{return}', [CategoryProductMenuHandler::class, 'execute']);

$bot->onCallbackQueryData('show_neuro_products {key},{value},{return}', [NeuroProductMenuHandler::class, 'execute']);

$bot->onCallbackQueryData('show_keyword_products {key},{value},{return}', [KeywordProductMenuHandler::class, 'execute']);

$bot->onCallbackQueryData('start_bestseller_products', [BestsellerProductMenuHandler::class, 'execute']);
// Menus

$bot->onCallbackQueryData('subscribe-{categoryId},{name},{userId}', [SearchMenu::class, 'subscribe']);

$bot->onCallbackQueryData('unsubscribe-{categoryId},{name},{userId}', [SearchMenu::class, 'unsubscribe']);


//Test
$bot->onCommand('test', function (Nutgram $bot) {
   $bot->sendMessage('t');
});
