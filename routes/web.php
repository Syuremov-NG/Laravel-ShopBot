<?php

use App\Bot\Menus\Product\SalesProductMenu;
use App\Http\Controllers\AuthController;
use App\Magento\Config\MageConfig;
use App\Models\Order;
use App\Models\User;
use App\Magento\Repository\MageRepository;
use danog\MadelineProto\API;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use SergiX44\Nutgram\Nutgram;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
//Route::get('/login', [AuthController::class, 'authenticate']);

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/getAnalyticMenus', [\App\Http\Controllers\AnalyticController::class, 'getMenus']);

Route::get('/test', function (MageConfig $mageConfig){
    print_r($mageConfig->getSupportUsername());
});
