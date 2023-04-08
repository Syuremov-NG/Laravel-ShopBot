<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Attributes\ParseMode;

class NotifyController extends Controller
{
    private Nutgram $bot;
    public function __construct(Nutgram $bot)
    {
        $this->bot = $bot;
    }

    public function __invoke(Request $request)
    {
        $chatId = $request->input('chatId');
        $status = $request->input('status');
        $order = $request->input('order');

        Log::debug("Data: $chatId $status $order");

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
}
