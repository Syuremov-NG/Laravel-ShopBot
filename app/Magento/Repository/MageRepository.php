<?php

namespace App\Magento\Repository;

use App\Magento\Config\MageConfig;
use App\Models\Category;
use App\Models\Product;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class MageRepository
{
    /**
     * @throws GuzzleException
     */
    public function getCategories(int $level = 2): Collection
    {
        $endpoint = "http://shop.local/rest/all/V1/categories/list"
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

    public function getChildrenCategories(string $parentId)
    {
        $endpoint = "http://shop.local/rest/all/V1/categories/list"
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
        $endpoint = "http://shop.local/rest/all/V1/products"
            . "?searchCriteria[filterGroups][0][filters][0][field]=category_id"
            . "&searchCriteria[filterGroups][0][filters][0][value]=$categoryId"
            . "&searchCriteria[pageSize]=$limit"
            . "&searchCriteria[currentPage]=$page";

        $client = new Client();
        try {
            $token = MageConfig::getAdminAuthToken();
            $headers = [
                'Authorization' => 'Bearer ' . trim($token, '"')
            ];
            $request = new Request('GET', $endpoint, $headers);
            $res = $client->send($request);
            $content = $res->getBody()->getContents();
            Log::debug($content);
            $arr = (array)json_decode($content)->items;
            return Product::hydrate($arr);
        } catch (GuzzleException $e) {
            return MageConfig::getAdminAuthToken();
        }
    }

    public function getProductsLike(string $field, string $value, int $limit, int $page): Collection
    {
        $endpoint = "http://shop.local/rest/all/V1/products"
            . "?searchCriteria[filterGroups][0][filters][0][field]=$field"
            . "&searchCriteria[filterGroups][0][filters][0][value]=%25$value%25"
            . "&searchCriteria[filterGroups][0][filters][0][conditionType]=like"
            . "&searchCriteria[pageSize]=$limit"
            . "&searchCriteria[currentPage]=$page";

        $client = new Client();
        try {
            $token = MageConfig::getAdminAuthToken();
            $headers = [
                'Authorization' => 'Bearer ' . trim($token, '"')
            ];
            $request = new Request('GET', $endpoint, $headers);
            $res = $client->send($request);
            $content = $res->getBody()->getContents();
            Log::debug($content);
            $arr = (array)json_decode($content)->items;
            return Product::hydrate($arr);
        } catch (GuzzleException $e) {
            return MageConfig::getAdminAuthToken();
        }
    }

    public function getCustomerId()
    {

    }
}
