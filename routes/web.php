<?php

use Illuminate\Support\Facades\Auth;
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
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/user/settings', [App\Http\Controllers\UserSettingController::class, 'index'])->name('user-settings');
Route::get('/help', [App\Http\Controllers\HelpController::class, 'index'])->name('help');
Route::get('/feedback', [App\Http\Controllers\HelpController::class, 'feedback'])->name('feedback');

Route::group(['middleware' => 'auth'], function() {
    Route::resource('folders', App\Http\Controllers\FolderController::class);
    Route::resource('bookmarks', App\Http\Controllers\BookmarkController::class);
});
//Route::get('tags', 'TagController@index');
//Route::get('tags/create', 'TagController@create');
//Route::get('larder/import', 'LarderController@store');
//Route::get('larder/tags/import', 'LarderController@storeTags');
//Route::get('larder/bookmarks/import', 'LarderController@storeBookmarks');
