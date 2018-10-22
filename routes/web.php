<?php

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

use Carbon\Carbon;
use App\Models\Book;

Route::get('/', function () {
    $data = factory(Book::class, 10)->make();

    App\Models\Book::insert($data->toArray());

    dd($data);
});
