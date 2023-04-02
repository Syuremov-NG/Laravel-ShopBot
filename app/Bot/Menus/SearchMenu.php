<?php

namespace App\Bot\Menus;

use App\Magento\Repository\MageRepository;
use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

class SearchMenu extends AbstractMenu
{
    public int $id;
    public User $user;

    public function __construct(
        protected MageRepository $mageRepository
    ) {
        parent::__construct();
    }

    public function start(Nutgram $bot)
    {
        $this->clearButtons();

        $this->menuText("Вы находитесь в меню поиска товара.\nВыберите один из доступных видов поиска:")
            ->addButtonRow(InlineKeyboardButton::make('Ручной поиск', callback_data: '@handleManual'))
            ->addButtonRow(InlineKeyboardButton::make('Поиск по ключевым словам', callback_data: '@handleKeywords'))
            ->addButtonRow(InlineKeyboardButton::make('Поиск по фото', callback_data: '@handlePhoto'))
            ->addButtonRow(InlineKeyboardButton::make("\xF0\x9F\x94\x99", callback_data: 'start'))
            ->orNext('none')
            ->showMenu();
    }

    public function handleManual(Nutgram $bot)
    {
        try {
            $categories = $this->mageRepository->getCategories();

            if (!$categories->count()) {
                $this->menuText("Что-то пошло не так, повторите попытку:");
                return;
            }
            $this->clearButtons();
            $this->menuText(
                "Выберите категорию товаров.".
                "\nКлик по категории, откроет дочерние категории".
                "\nКлик по стрелочке рядом, откроет товары в категории"
            );
            foreach ($categories as $category) {
                $this->addButtonRow(
                    InlineKeyboardButton::make($category->name, callback_data: $category->id . "," . $category->name . '@handleCategory'),
                    InlineKeyboardButton::make("\xE2\x9E\xA1", callback_data: 'show_products ' . $category->id)
                );
            }
            $this->addButtonRow(InlineKeyboardButton::make("\xF0\x9F\x94\x99", callback_data: '@start'));
            $this->showMenu();
        } catch (GuzzleException $e) {
            Log::error($e);
        }
    }

    public function handleCategory(Nutgram $bot)
    {
        $data = $bot->callbackQuery()->data;
        list($id, $name) = explode(',', $data);

        try {
            $categories = $this->mageRepository->getChildrenCategories($id);

            if (!$categories->count()) {
                $this->menuText("Больше нет вложенных категорий.");
            } else {
                $this->menuText(
                    "Вы находитесь в категории: $name.\nВыберите категорию товаров.".
                    "\nКлик по категории, откроет дочерние категории".
                    "\nКлик по стрелочке рядом, откроет товары в категории"
                );
            }

            $this->clearButtons();
            $this->addButtonRow(
                InlineKeyboardButton::make("Показать товары в этой категории.", callback_data: 'show_products ' . $id)
            );
            foreach ($categories as $category) {
                $this->addButtonRow(
                    InlineKeyboardButton::make($category->name, callback_data: $category->id . "," . $category->name . '@handleCategory'),
                    InlineKeyboardButton::make("\xE2\x9E\xA1", callback_data: 'show_products ' . $category->id)
                );
            }
            $this->addButtonRow(InlineKeyboardButton::make("\xF0\x9F\x94\x99", callback_data: 'categories'));
            $this->showMenu();
        } catch (GuzzleException $e) {
            Log::error($e);
        }
    }

    public function handleKeywords(Nutgram $bot)
    {
        $this->clearButtons();
        $this->menuText('Напишите полное или частичное название товара.')->showMenu();
        $this->next('continueKeyword');
    }

    public function continueKeyword(Nutgram $bot)
    {
        $answer = $bot->message()->text;

        $bot->setData('keyword', $answer);
        ProductMenu::begin($bot);

//        $this->menuText("Ваш запрос: $answer.");
//        $this->addButtonRow(InlineKeyboardButton::make('Продолжить', callback_data: "keyword $answer"));
    }

    public function handlePhoto(Nutgram $bot)
    {

    }
}
