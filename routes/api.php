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

Route::delete('deleted-books/{deletedBook}', 'BookController@forceDestroy')->name('books.force_destroy');
Route::resource('books', 'BookController')->except(['create', 'edit']);

Route::delete('deleted-notes/{deletedNote}', 'NoteController@forceDestroy')->name('notes.force_destroy');
Route::resource('notes', 'NoteController')->except(['store', 'create', 'edit']);
Route::post('books/{book}/notes', 'NoteController@store')->name('notes.store');

Route::resource('tags', 'TagController')->except(['store', 'create', 'edit']);

Route::post('images', 'ImageController@store')->name('images.store');

Route::delete('deleted-posts/{deletedPost}', 'PostController@forceDestroy')->name('posts.force_destroy');
Route::resource('posts', 'PostController');
