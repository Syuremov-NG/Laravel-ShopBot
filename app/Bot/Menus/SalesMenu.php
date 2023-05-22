<?php

namespace App\Bot\Menus;

use App\Bot\CallbackHandler\ProductMenu\SalesProductMenuHandler;
use App\Magento\Repository\MageRepository;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

class SalesMenu extends AbstractMenu
{
    public function __construct(protected MageRepository $mageRepository)
    {
        parent::__construct();
    }

    public function start(Nutgram $bot)
    {
        $this->clearButtons();
        $this->menuText("Выберите категорию акций:")
            ->addButtonRow(InlineKeyboardButton::make('Акции на категории товаров', callback_data: 'category_sales@handleButton'))
            ->addButtonRow(InlineKeyboardButton::make('Акции на определенные товары', callback_data: 'product_sales@handleButton'))
            ->addButtonRow(InlineKeyboardButton::make('Другие акции', callback_data: 'other_sales@handleButton'));
        $this->getReturnButton('start');
        $this->orNext('none')->showMenu();
    }

    public function handleButton(Nutgram $bot) {
        $data = $bot->callbackQuery()->data;
        switch ($data) {
            case 'category_sales':
                $this->handleCategory($bot);
                break;
            case 'product_sales':
                $this->handleProduct($bot);
                break;
            case 'other_sales':
                $this->handleOther($bot);
                break;
        }
    }

    private function handleCategory(Nutgram $bot)
    {
        $this->clearButtons();
        $categorySales = $this->mageRepository->getCategorySales();
        $this->menuText("Акции на категории товаров:");

        foreach ($categorySales as $sale) {
            $this->addButtonRow(InlineKeyboardButton::make($sale->name, callback_data: $sale->id . '@handleCategorySale'));
        }
        $this->getReturnButton();

        $this->showMenu();
    }

    public function handleCategorySale(Nutgram $bot)
    {
        $this->clearButtons();
        $id = $bot->callbackQuery()->data;
        $sale = $this->mageRepository->getSaleInfo($id);

        $this->menuText("Категории участвующие в акции \n" . '"' . $sale->name . '"'
            . "\nОписание акции: " . $sale->description);

        function foo ($item) {
            $ret = null;
            foreach ($item as $condition) {
                if ($condition->attribute_name ?? '' == 'category_ids') {
                    return $condition;
                }
                $ret = foo($condition->conditions);
            }
            return $ret;
        };

        $categoryCondition = foo($sale->condition->conditions);
        $categoryIds = explode(',', $categoryCondition->value);

        $categories = $this->mageRepository->getCategoriesByIds($categoryIds);

        foreach ($categories as $category) {
            $this->addButtonRow(
                InlineKeyboardButton::make($category->name, callback_data: 'show_category_products category-' . $category->id . ',start_sales_menu')
            );
        }
        $this->addButtonRow(InlineKeyboardButton::make("\xF0\x9F\x94\x99", callback_data: '@start'));
        $this->showMenu();
    }

    private function handleProduct(Nutgram $bot)
    {
        $this->clearButtons();
        $categorySales = $this->mageRepository->getProductSales();
        $this->menuText("Акции на категории товаров:");

        foreach ($categorySales as $sale) {
            $this->addButtonRow(InlineKeyboardButton::make($sale->name, callback_data: $sale->id . '@handleProductSale'));
        }
        $this->getReturnButton();

        $this->showMenu();
    }

    public function handleProductSale(Nutgram $bot)
    {
        $this->clearButtons();
        $id = $bot->callbackQuery()->data;
        $sale = $this->mageRepository->getSaleInfo($id);

        function foo ($item) {
            $ret = null;
            foreach ($item as $condition) {
                if ($condition->attribute_name ?? '' == 'sku') {
                    return $condition;
                }
                $ret = foo($condition->conditions);
            }
            return $ret;
        }

        $productCondition = foo($sale->condition->conditions);
        $skuList = explode(', ', $productCondition->value);
        $this->end();
        SalesProductMenuHandler::execute($bot, 'sku_list', $skuList, 'start_sales_menu');
    }

    private function handleOther(Nutgram $bot)
    {
        $this->clearButtons();
        $categorySales = $this->mageRepository->getOtherSales();
        $this->menuText("Другие акции в нашем магазине:");

        foreach ($categorySales as $sale) {
            $this->addButtonRow(InlineKeyboardButton::make($sale->name, callback_data: $sale->id . '@handleOtherSale'));
        }
        $this->getReturnButton();

        $this->showMenu();
    }

    public function handleOtherSale(Nutgram $bot)
    {
        $this->clearButtons();
        $id = $bot->callbackQuery()->data;
        $sale = $this->mageRepository->getSaleInfo($id);

        $this->menuText("Акция\n" . '"' . $sale->name . '"'
            . "\nОписание акции: " . $sale->description);
        $this->addButtonRow(InlineKeyboardButton::make("\xF0\x9F\x94\x99", callback_data: '@start'));
        $this->showMenu();
    }

    public function getReturnButton(string $callbackData = 'start_sales_menu')
    {
        $this->addButtonRow(InlineKeyboardButton::make("\xF0\x9F\x94\x99", callback_data: $callbackData));
    }

    public function none(Nutgram $bot)
    {
        $this->end();
    }
}
