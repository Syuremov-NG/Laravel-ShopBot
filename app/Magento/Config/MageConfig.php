<?php

namespace App\Magento\Config;

use App\Models\AdminToken;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class MageConfig
{
    public function __construct()
    {
    }

    /**
     * @throws GuzzleException
     */
    public static function getAdminAuthToken()
    {
        $items = AdminToken::all();
        $token = $items->count() != 0 ? $items->last() : null;
        $currentDate = Carbon::now();

        if (
            $token?->token
            && $currentDate->diffInSeconds(Carbon::parse($token->created_at)) < config('global.token_lifetime')
        ) {
            Log::debug('actual');
            return $token->token;
        }
        Log::debug('new');
        $token = new AdminToken();
        $endpoint = config('global.magento_url') . "/rest/all/V1/integration/admin/token";
        $client = new Client();
        $res = $client->request('post', $endpoint, ['json' => [
            'username' => env('MAGENTO_ADMIN_LOGIN'), 'password' => env('MAGENTO_ADMIN_PASS')
        ]]);
        $content = $res->getBody();
        $token->token = trim($content, '"');
        $token->save();

        return $token->token;
    }

    /**
     * @throws GuzzleException
     */
    public function getCustomerAuthToken(string $email, string $password, string $chatId)
    {
        $endpoint = config('global.magento_url') . "/rest/all/V1/integration/customer/token";
        $client = new Client();
        $res = $client->request('post', $endpoint, ['json' => [
            'username' => $email, 'password' => $password
        ]]);
        $content = $res->getBody()->getContents();
        User::where(User::TELEGRAM_ID, $chatId)->update([
            User::TOKEN => trim($content, '"'),
            User::TOKEN_UPDATED => Carbon::now()->toDateTimeString()
        ]);
        return $content;
    }

    /**
     * @throws GuzzleException
     */
    public function sendChatId(mixed $email, mixed $chatId)
    {
        $endpoint = config('global.magento_url') . "/rest/all/V1/chatbot/setChatId/";
        $client = new Client();
        $client->request('post', $endpoint, ['json' => [
            'email' => $email, 'chatId' => $chatId
        ]]);
    }

    public function sendInviteLink(string $link)
    {
        $endpoint = config('global.magento_url') . "/rest/all/V1/chatbot/addInviteLink?link=$link";
        $client = new Client();
        $client->request('get', $endpoint);
    }

    /**
     * @throws GuzzleException
     */
    public function getSupportUsername(): string
    {
        $endpoint = config('global.magento_url') . "/rest/all/V1/chatbot/getSupportUsername/";
        $client = new Client();
        $body = json_decode($client->request('get', $endpoint)->getBody()->getContents());
        return $body;
    }

    private function update_env($data = []) : void
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            foreach ($data as $key => $value) {
                file_put_contents($path, str_replace(
                    $key . '="' . env($key).'"', $key . '=' . $value, file_get_contents($path)
                ));
            }
        }
    }
}
