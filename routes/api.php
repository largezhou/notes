<?php

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

Route::group([
    'prefix' => 'auth',
], function () {
    Route::post('login', 'AuthController@login')->name('login');
    Route::post('logout', 'AuthController@logout')->middleware('auth')->name('logout');
    Route::get('info', 'AuthController@info')->middleware('auth')->name('info');
});

Route::delete('deleted-books/{id}', 'BookController@forceDestroy')->name('books.force_destroy');
Route::resource('books', 'BookController')->except(['create', 'edit']);

Route::delete('deleted-notes/{id}', 'NoteController@forceDestroy')->name('notes.force_destroy');
Route::resource('notes', 'NoteController')->except(['store', 'create', 'edit']);
Route::post('books/{book}/notes', 'NoteController@store')->name('notes.store');

Route::resource('tags', 'TagController')->except(['store', 'show', 'create', 'edit']);

Route::post('image', 'ImageController@store')->name('images.store');
