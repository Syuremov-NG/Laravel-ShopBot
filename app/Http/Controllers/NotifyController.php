<?php

namespace App\Http\Controllers;

use App\Magento\Repository\MageRepository;
use App\Models\Subscriber;
use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Attributes\ParseMode;

class NotifyController extends Controller
{
    private Nutgram $bot;
    public function __construct(Nutgram $bot, private MageRepository $mageRepository)
    {
        $this->bot = $bot;
    }

    public function notifyOrder(Request $request)
    {
        $chatId = $request->input('chatId');
        $status = $request->input('status');
        $order = $request->input('order');

        $user = User::where(User::TELEGRAM_ID, $chatId)->first();

        if ($user->notify) {
            $this->bot->sendMessage(
                "Среди ваших заказов есть изменения:\n<b>$order</b> изменил статус на <b>$status</b>.",
                [
                    'chat_id' => $chatId,
                    'parse_mode' => ParseMode::HTML
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    public function notifyPromo(Request $request)
    {
        $categories = $request->input('categories');
        $promo = $request->input('promo_name');
        Log::debug("NOTIFY 48: " . $categories);

        if (!$categories) {
            return response(['success' => false], 400);
        }

        foreach (explode(',', $categories) as $category) {
            $subscribers = Subscriber::where('category_id', $category)->get();
            foreach ($subscribers as $subscriber) {
                $user = User::where('telegram_id', $subscriber->user_id)->first();
                $category = $this->mageRepository->getCategoriesByIds([$category])->first();
                $path = explode('/', $category->path);
                $names = [];
                foreach ($path as $categoryId) {
                    $names[] = $this->mageRepository->getCategoriesByIds([$categoryId])->first()->name;
                }

                $path = implode('/', array_slice($names, 2));

                $this->bot->sendMessage(
                    "На категорию <b>$path</b> появилась новая акция: \n <b>$promo</b>.",
                    [
                        'chat_id' => $user->telegram_id,
                        'parse_mode' => ParseMode::HTML
                    ]
                );
            }
        }

        return response()->json(['success' => true]);
    }
}
