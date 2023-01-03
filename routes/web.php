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

Route::get('/', function () {
    return view('welcome');
})->middleware(['auth', 'model']);


Route::get('/login', 'App\Http\Controllers\Auth\LoginController@login')->name('login');
Route::get('/stepup/{method}', 'App\Http\Controllers\Auth\LoginController@stepup')->name('stepup');
Route::post('/logout', 'App\Http\Controllers\Auth\LoginController@login')->name('logout');

Route::name('mfa.')
->prefix('mfa')
->group(function() {
    Route::get('/', 'App\Http\Controllers\MfaController@showOverview')->name('home');
    Route::get('/gauth', 'App\Http\Controllers\MfaController@showGauth')->name('gauth');
    Route::get('/webauthn', 'App\Http\Controllers\MfaController@showGauth')->name('webauthn');
    Route::get('/sms', 'App\Http\Controllers\MfaController@showGauth')->name('sms');
});