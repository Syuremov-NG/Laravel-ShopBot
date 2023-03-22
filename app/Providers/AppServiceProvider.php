<?php

namespace App\Providers;

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
