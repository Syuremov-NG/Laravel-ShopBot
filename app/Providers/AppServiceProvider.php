<?php

namespace App\Providers;

use App\Bot\Menus\OrdersMenu;
use App\Bot\Menus\Product\CategoryProductMenu;
use App\Bot\Menus\SearchMenu;
use App\Bot\Menus\StartMenu;
use App\Http\Controllers\AuthController;
use App\Magento\Config\MageConfig;
use App\Magento\Repository\MageRepository;
use App\Neuro\ValidImage;
use Illuminate\Support\ServiceProvider;
use SergiX44\Nutgram\Nutgram;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(MageConfig::class, function ($app) {
            return new MageConfig();
        });

        $this->app->bind(AuthController::class, function ($app) {
            return new AuthController($app->make(MageConfig::class), $app->make(Nutgram::class));
        });

        $this->app->bind(MageRepository::class, function ($app) {
            return new MageRepository();
        });

        $this->app->bind(OrdersMenu::class, function ($app) {
            return new OrdersMenu($app->make(MageRepository::class));
        });

        $this->app->bind(SearchMenu::class, function ($app) {
            return new SearchMenu(
                $app->make(MageRepository::class),
                $app->make(ValidImage::class)
            );
        });

        $this->app->bind(CategoryProductMenu::class, function ($app) {
            return new CategoryProductMenu($app->make(MageRepository::class));
        });

        $this->app->bind(ValidImage::class, function ($app) {
            return new ValidImage();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
