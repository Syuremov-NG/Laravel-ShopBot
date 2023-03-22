<?php

namespace App\Magento\Config;

use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class MageConfig
{
    public function __construct()
    {
    }

    /**
     * @throws GuzzleException
     */
    public function getAdminAuthToken()
    {
        $endpoint = "http://shop.local/rest/all/V1/integration/admin/token";
        $client = new Client();
        $res = $client->request('post', $endpoint, ['json' => [
            'username' => 'admin', 'password' => 'admin0000'
        ]]);
        $content = $res->getBody();
        $this->update_env(['MAGENTO_ACCESS_TOKEN' => $content]);
        return $content;
    }

    /**
     * @throws GuzzleException
     */
    public function getCustomerAuthToken(string $email, string $password, string $chatId)
    {
        $endpoint = "http://shop.local/rest/all/V1/integration/customer/token";
        $client = new Client();
        $res = $client->request('post', $endpoint, ['json' => [
            'username' => $email, 'password' => $password
        ]]);
        $content = $res->getBody()->getContents();
        User::where(User::TELEGRAM_ID, $chatId)->update([
            User::TOKEN => $content,
            User::TOKEN_UPDATED => Carbon::now()->toDateTimeString()
        ]);
        return $content;
    }

    private function update_env( $data = [] ) : void
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
