<?php

use HtmlBookmarks\Http\Controllers\BookmarkFileImportController;
use HtmlBookmarks\Http\Controllers\HtmlBookmarkController;
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
    Route::resource('imports', BookmarkFileImportController::class);
    Route::post('/import', [HtmlBookmarkController::class, 'store'])->name('bookmarks.import');
    Route::get('/files/{file}', [HtmlBookmarkController::class, 'show'])->name('bookmarks.files.show');
});
