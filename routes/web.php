<?php

use App\Http\Controllers\AuthController;
use App\Models\User;
use App\Magento\Repository\MageRepository;
use App\Models\User2;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;
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
Route::get('/test', function (MageRepository $mageRepository) {
    $product = $mageRepository->getProducts(3, 1, 1)->last();
    // SKU, name, price, media_gallery_entries['file'], "attribute_code": "description"
    echo $product->sku;
    echo $product->name;
    echo $product->price;
    echo $product->media_gallery_entries[0]->file;
//    print_r($product);
    $arr = array_filter($product->custom_attributes, function ($item) {
            return $item->attribute_code == 'description';
        });

    $obj = reset($arr)->value;
    print_r($obj);
});


