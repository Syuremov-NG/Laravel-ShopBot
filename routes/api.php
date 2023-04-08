<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotifyController;
use App\Magento\Config\MageConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Webhook;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// php artisan nutgram:hook:set ***/api/webhook
Route::post('/webhook', [\App\Http\Controllers\FrontController::class, 'handle']);
Route::post('/login', [AuthController::class, 'authenticate']);
Route::post('/notifyOrder', NotifyController::class);
