<?php

namespace App\Providers;

use App\Bot\Menus\OrdersMenu;
use App\Bot\Menus\ProductMenu;
use App\Bot\Menus\SearchMenu;
use App\Bot\Menus\StartMenu;
use App\Http\Controllers\AuthController;
use App\Magento\Config\MageConfig;
use App\Magento\Repository\MageRepository;
use Illuminate\Support\Facades\Auth;
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

        $this->app->bind(StartMenu::class, function ($app) {
            return new StartMenu(
                $app->make(SearchMenu::class),
                $app->make(OrdersMenu::class)
            );
        });

        $this->app->bind(SearchMenu::class, function ($app) {
            return new SearchMenu($app->make(MageRepository::class));
        });

        $this->app->bind(ProductMenu::class, function ($app) {
            return new ProductMenu($app->make(MageRepository::class));
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
