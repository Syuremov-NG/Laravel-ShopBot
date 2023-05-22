<?php

namespace App\Http\Controllers;

use App\Bot\Menus\StartMenu;
use App\Magento\Config\MageConfig;
use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class AuthController extends Controller
{
    private MageConfig $mageConfig;
    private Nutgram $bot;

    public function __construct(MageConfig $mageConfig, Nutgram $bot)
    {
        $this->mageConfig = $mageConfig;
        $this->bot = $bot;
    }

    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request):  Application|ResponseFactory|Response
    {
        $formData = $request->toArray();
        $email = $formData['email'];
        $password = $formData['password'];
        $chatId = $formData['id'];
        $messageId = $formData['message'];
        try {
            $this->mageConfig->getCustomerAuthToken($email, $password, $chatId);
            $this->mageConfig->sendChatId($email, $chatId);
            $errorMessage = User::where(User::TELEGRAM_ID, $chatId)->first()->last_message;

            try {
                $this->bot->deleteMessage($chatId, $messageId);
            } catch (\Exception $ignore) {}
            try {
                $this->bot->deleteMessage($chatId, $errorMessage);
            } catch (\Exception $ignore) {}

            $message = $this->bot->sendMessage('Успех! Вы можете продолжить.', [
                'chat_id' => $chatId,
                'reply_markup' => InlineKeyboardMarkup::make()->addRow(
                    InlineKeyboardButton::make('Продолжить!', callback_data: 'auth_success'),
                )
            ]);
            User::where(User::TELEGRAM_ID, $chatId)->update([
                User::LAST_MESSAGE => $message->message_id
            ]);
            return response(['Success!']);
        } catch (GuzzleException $exception) {
            if ($exception->getCode() == 401) {
                $errorMessage = User::where(User::TELEGRAM_ID, $chatId)->first()->last_message;

                try {
                    $this->bot->deleteMessage($chatId, $messageId);
                } catch (\Exception $ignore) {}
                try {
                    $this->bot->deleteMessage($chatId, $errorMessage);
                } catch (\Exception $ignore) {}
                Log::debug('send');
                $message = $this->bot->sendMessage('Не получилось авторизироваться. Вернитесь обратно и повторите попытку.', [
                    'chat_id' => $chatId,
                    'reply_markup' => InlineKeyboardMarkup::make()->addRow(
                        InlineKeyboardButton::make('Вернуться!', callback_data: 'auth_failed'),
                    )
                ]);
                User::where(User::TELEGRAM_ID, $chatId)->update([
                    User::LAST_MESSAGE => $message->message_id
                ]);
                return response('Not auth', $exception->getCode());
            }
            Log::error($exception->getMessage());
            $message = 'Oops, something went wrong.';
            $this->bot->sendMessage($message, ['chat_id' => $chatId]);
            return response([$message], 500);
        }
    }
}
