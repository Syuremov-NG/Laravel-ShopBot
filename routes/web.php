<?php

use App\Http\Controllers\AuthController;
use App\Models\User;
use App\Magento\Repository\MageRepository;
use GuzzleHttp\Client;
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
Route::get('/test', function () {
//    $response = Http::withHeaders([
//        'Content-Type' => 'multipart/form-data'
//    ])->attach('imageData', file_get_contents("/home/nsyuremov/Study/Diplom/ShopBot/valid/backpack puma_phase_backpack (2).png"), 'image.jpg')
//        ->post('http://shop.local/rest/all/V1/chatbot/validImage/');
//    if ($response->successful()) {
//        echo 'test';
//    } else {
//        echo $response->getBody();
//    }
    $client = new Client();

    $response = $client->request('POST', 'http://shop.local/rest/all/V1/chatbot/validImage', [
        'multipart' => [
            [
                'name' => 'image',
                'contents' => fopen('/home/nsyuremov/Study/Diplom/ShopBot/valid/backpack puma_phase_backpack (2).png', 'r'),
                'filename' => 'image.png'
            ]
        ]
    ]);

    echo $response->getBody();
});


