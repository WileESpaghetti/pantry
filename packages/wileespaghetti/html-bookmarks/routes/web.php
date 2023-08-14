<?php

use HtmlBookmarks\Http\Controllers\BookmarkFileController;
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
    Route::resource('imports', BookmarkFileController::class);
    Route::post('/import', [BookmarkFileController::class, 'store'])->name('bookmarks.import');
    Route::get('/files/{file}', [BookmarkFileController::class, 'show'])->name('bookmarks.files.show');
});
