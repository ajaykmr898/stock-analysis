<?php

use App\Http\Controllers\StockController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    $controller = new StockController();
    $data = $controller->getData();

    return view('home', ['data' => $data]);
});

Route::post('/data', function (Request $request) {
    $c = new StockController();
    return $c->fetchData($request);
});
