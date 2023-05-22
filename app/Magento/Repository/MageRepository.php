<?php

namespace App\Magento\Repository;

use App\Magento\Config\MageConfig;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use stdClass;

class MageRepository
{
    /**
     * @throws GuzzleException
     */
    public function getCategories(int $level = 2): Collection
    {
        $endpoint = config('global.magento_url') . "/rest/all/V1/categories/list"
            . "?searchCriteria[filterGroups][0][filters][0][field]=level"
            . "&searchCriteria[filterGroups][0][filters][0][value]=$level";

        $client = new Client();
        try {
            $token = MageConfig::getAdminAuthToken();
            $headers = [
                'Authorization' => "Bearer " . trim($token, '"')
            ];
            $request = new Request('GET', $endpoint, $headers);
            $res = $client->send($request);
            $content = $res->getBody()->getContents();
            $arr = (array)json_decode($content)->items;
            return Category::hydrate($arr);
        } catch (GuzzleException $e) {
            Log::error($e);
            return MageConfig::getAdminAuthToken();
        }
    }

    public function getCategoriesByIds(array $ids)
    {
        $endpoint = config('global.magento_url') . "/rest/all/V1/categories/list"
            . "?searchCriteria[filterGroups][0][filters][0][field]=entity_id"
            . "&searchCriteria[filterGroups][0][filters][0][value]=" . implode(',', $ids)
            . "&searchCriteria[filterGroups][0][filters][0][conditionType]=in";

        $client = new Client();
        try {
            $token = MageConfig::getAdminAuthToken();
            $headers = [
                'Authorization' => "Bearer " . trim($token, '"')
            ];
            $request = new Request('GET', $endpoint, $headers);
            $res = $client->send($request);
            $content = $res->getBody()->getContents();
            $arr = (array)json_decode($content)->items;
            return Category::hydrate($arr);
        } catch (GuzzleException $e) {
            return MageConfig::getAdminAuthToken();
        }
    }

    public function getChildrenCategories(string $parentId)
    {
        $endpoint = config('global.magento_url') . "/rest/all/V1/categories/list"
            . "?searchCriteria[filterGroups][0][filters][0][field]=parent_id"
            . "&searchCriteria[filterGroups][0][filters][0][value]=$parentId";

        $client = new Client();
        try {
            $token = MageConfig::getAdminAuthToken();
            $headers = [
                'Authorization' => "Bearer " . trim($token, '"')
            ];
            $request = new Request('GET', $endpoint, $headers);
            $res = $client->send($request);
            $content = $res->getBody()->getContents();
            $arr = (array)json_decode($content)->items;
            $collection = Category::hydrate($arr);
            foreach ($collection as $item) {
                Log::debug($item->name);
            }
            return $collection;
        } catch (GuzzleException $e) {
            Log::error($e);
            return MageConfig::getAdminAuthToken();
        }
    }

    public function getProducts(string $categoryId, int $limit, int $page): Collection
    {
        $endpoint = config('global.magento_url') . "/rest/all/V1/products"
            . "?searchCriteria[filterGroups][0][filters][0][field]=category_id"
            . "&searchCriteria[filterGroups][0][filters][0][value]=$categoryId"
            . "&searchCriteria[pageSize]=$limit"
            . "&searchCriteria[currentPage]=$page";

        $client = new Client();
        try {
            $token = MageConfig::getAdminAuthToken();
            $arr = $this->getItems($token, $endpoint, $client);
            return Product::hydrate($arr);
        } catch (GuzzleException $e) {
            return MageConfig::getAdminAuthToken();
        }
    }

    public function getBestsellerProducts(int $page): Collection
    {
        $endpoint = config('global.magento_url') . "/rest/all/V1/chatbot/getBestsellers/?curPage=$page";

        $client = new Client();
        try {
            $token = MageConfig::getAdminAuthToken();
            $arr = $this->getItems($token, $endpoint, $client);
            return Product::hydrate($arr);
        } catch (GuzzleException $e) {
            return MageConfig::getAdminAuthToken();
        }
    }

    public function getProductsLike(string $field, string $value, int $limit, int $page): Collection
    {
        $endpoint = config('global.magento_url') . "/rest/all/V1/products"
            . "?searchCriteria[filterGroups][0][filters][0][field]=$field"
            . "&searchCriteria[filterGroups][0][filters][0][value]=%25$value%25"
            . "&searchCriteria[filterGroups][0][filters][0][conditionType]=like"
            . "&searchCriteria[pageSize]=$limit"
            . "&searchCriteria[currentPage]=$page";

        $client = new Client();
        try {
            $token = MageConfig::getAdminAuthToken();
            $arr = $this->getItems($token, $endpoint, $client);
            return Product::hydrate($arr);
        } catch (GuzzleException $e) {
            return MageConfig::getAdminAuthToken();
        }
    }

    public function getProductBySku(string $sku): ?stdClass
    {
        $endpoint = config('global.magento_url') . "/rest/all/V1/products/$sku";

        $client = new Client();
        try {
            $token = MageConfig::getAdminAuthToken();
            $headers = [
                'Authorization' => 'Bearer ' . trim($token, '"')
            ];
            $request = new Request('GET', $endpoint, $headers);
            $res = $client->send($request);
            return json_decode($res->getBody()->getContents());
        } catch (GuzzleException $e) {
            Log::error("Error on SKU: " . $sku);
            Log::error($e);
            MageConfig::getAdminAuthToken();
            if ($e->getCode() == 401) {
                return $this->getProductBySku($sku);
            }
        }
        return null;
    }

    public function getProductsByType(string $value, int $limit, int $page)
    {
        Log::debug('value: ' . $value);
        $endpoint = config('global.magento_url') . "/rest/all/V1/products"
            . "?searchCriteria[filterGroups][0][filters][0][field]=neuro_label"
            . "&searchCriteria[filterGroups][0][filters][0][value]=$value"
            . "&searchCriteria[pageSize]=$limit"
            . "&searchCriteria[currentPage]=$page";

        $client = new Client();
        try {
            $token = MageConfig::getAdminAuthToken();
            $arr = $this->getItems($token, $endpoint, $client);
            return Product::hydrate($arr);
        } catch (GuzzleException $e) {
            return MageConfig::getAdminAuthToken();
        }
    }

    public function getCustomerId(string $chatId)
    {
        $endpoint = config('global.magento_url') . "/rest/V1/customers/me";
        $user = User::firstOrCreate([User::TELEGRAM_ID => $chatId]);
        $headers = [
            'Authorization' => 'Bearer ' . trim($user->token, '"')
        ];
        $client = new Client();
        try {
            $request = new Request('GET', $endpoint, $headers);
            $res = $client->send($request);
            $content = $res->getBody()->getContents();
            Log::debug($content);
            return json_decode($content)->id;
        } catch (GuzzleException $e) {
            return null;
        }
    }

    public function getOrders(string $chatId)
    {
        $id = $this->getCustomerId($chatId);
        $client = new Client();
        if (!$id) {
            return null;
        }
        try {
            $token = MageConfig::getAdminAuthToken();
            $endpoint = config('global.magento_url') . "/rest/V1/orders"
                . "?searchCriteria[filterGroups][0][filters][0][field]=customer_id"
                . "&searchCriteria[filterGroups][0][filters][0][value]=$id";
            $arr = $this->getItems($token, $endpoint, $client);
            return Order::hydrate($arr);
        } catch (GuzzleException $exception) {
            return false;
        }
    }

    public function getCategorySales()
    {
        $client = new Client();
        try {
            $token = MageConfig::getAdminAuthToken();
            $endpoint = config('global.magento_url') . "/rest/V1/salesrules/getCategorySales/";
            $headers = [
                'Authorization' => 'Bearer ' . trim($token, '"')
            ];
            $request = new Request('GET', $endpoint, $headers);
            $res = $client->send($request);
            $content = $res->getBody()->getContents();
            $arr = (array)json_decode($content)[0]->items;
            return Sale::hydrate($arr);
        } catch (GuzzleException $exception) {
            return false;
        }
    }

    public function getProductSales()
    {
        $client = new Client();
        try {
            $token = MageConfig::getAdminAuthToken();
            $endpoint = config('global.magento_url') . "/rest/V1/salesrules/getProductSales/";
            $headers = [
                'Authorization' => 'Bearer ' . trim($token, '"')
            ];
            $request = new Request('GET', $endpoint, $headers);
            $res = $client->send($request);
            $content = $res->getBody()->getContents();
            $arr = (array)json_decode($content)[0]->items;
            return Sale::hydrate($arr);
        } catch (GuzzleException $exception) {
            return false;
        }
    }

    public function getOtherSales()
    {
        $client = new Client();
        try {
            $token = MageConfig::getAdminAuthToken();
            $endpoint = config('global.magento_url') . "/rest/V1/salesrules/getOtherSales/";
            $headers = [
                'Authorization' => 'Bearer ' . trim($token, '"')
            ];
            $request = new Request('GET', $endpoint, $headers);
            $res = $client->send($request);
            $content = $res->getBody()->getContents();
            $arr = (array)json_decode($content)[0]->items;
            return Sale::hydrate($arr);
        } catch (GuzzleException $exception) {
            return false;
        }
    }

    public function getSaleInfo(string $id)
    {
        $client = new Client();
        try {
            $token = MageConfig::getAdminAuthToken();
            $endpoint = config('global.magento_url') . "/rest/V1/salesRules/$id";
            $headers = [
                'Authorization' => 'Bearer ' . trim($token, '"')
            ];
            $request = new Request('GET', $endpoint, $headers);
            $res = $client->send($request);
            $content = json_decode($res->getBody()->getContents());
            return $content;
        } catch (GuzzleException $exception) {
            return false;
        }
    }

    /**
     * @param mixed $token
     * @param string $endpoint
     * @param Client $client
     * @return array
     * @throws GuzzleException
     */
    public function getItems(mixed $token, string $endpoint, Client $client): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . trim($token, '"')
        ];
        $request = new Request('GET', $endpoint, $headers);
        $res = $client->send($request);
        $content = $res->getBody()->getContents();
        return (array)json_decode($content)->items;
    }
}
