<?php

use Illuminate\Support\Facades\Route;
use Pantry\Http\Controllers\BookmarkController;
use Pantry\Http\Controllers\FolderController;
use Pantry\Http\Controllers\TagController;

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
    Route::delete('tags', [TagController::class, 'deleteMany'])->name('tags.destroyMany');
    Route::resource('tags', TagController::class);
    Route::resource('folders', FolderController::class);
    Route::resource('bookmarks', BookmarkController::class);
});

