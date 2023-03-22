<?php

namespace App\Magento\Repository;

use App\Magento\Config\MageConfig;
use App\Models\Product;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;

class MageRepository
{
    public function getProducts(string $chatId) {
        $endpoint = "http://shop.local/rest/all/V1/products?searchCriteria[filterGroups][0][filters][0][field]=category_id&searchCriteria[filterGroups][0][filters][0][value]=2";
        $client = new Client();
        try {
            $headers = [
                'Authorization' => 'Bearer ' . User::where(User::TELEGRAM_ID, $chatId)->token
            ];
            $request = new Request('GET', $endpoint, $headers);
            $res = $client->send($request);
            $content = $res->getBody()->getContents();
            $arr = (array)json_decode($content)->items;
            $collection = Product::hydrate($arr);
            $collection = $collection->flatten();
            return $content;
        } catch (ClientException $e) {
            $conf = new MageConfig();
            return $conf->getAdminAuthToken();
        }
    }
}
