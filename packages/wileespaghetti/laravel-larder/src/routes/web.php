<?php
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'login/larder', 'middleware' => 'web'], function() {
    Route::get('/', 'Larder\Http\Controllers\Auth\LarderAuthController@redirectToProvider')->name('login/larder');
    Route::get('/callback', 'Larder\Http\Controllers\Auth\LarderAuthController@handleProviderCallback');
});
