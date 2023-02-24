<?php

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

Route::group(['middleware' => ['web', 'auth']], function() {
    Route::delete('tags', [App\Http\Controllers\TagController::class, 'deleteMany'])->name('tags.destroyMany');
    Route::resource('tags', App\Http\Controllers\TagController::class);
    Route::resource('folders', App\Http\Controllers\FolderController::class);
    Route::resource('bookmarks', App\Http\Controllers\BookmarkController::class);
});

