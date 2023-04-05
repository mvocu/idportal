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
    Route::get('/policy', 'App\Http\Controllers\MfaController@showPolicy')->name('policy');
    Route::post('/policy', 'App\Http\Controllers\MfaController@setPolicy')->name('policy.set');
    Route::get('/gauth', 'App\Http\Controllers\MfaController@showGauth')->name('gauth');
    Route::delete('/gauth', 'App\Http\Controllers\MfaController@deleteGauth')->name('gauth.delete');
    Route::post('/gauth', 'App\Http\Controllers\MfaController@performGauth')->name('gauth.add');
    Route::get('/gauth/test', 'App\Http\Controllers\MfaController@performGauth')->name('gauth.test');
    Route::get('/webauthn', 'App\Http\Controllers\MfaController@showWebAuthn')->name('webauthn');
    Route::delete('/webauthn', 'App\Http\Controllers\MfaController@deleteWebAuthn')->name('webauthn.delete');
    Route::post('/webauthn', 'App\Http\Controllers\MfaController@performWebAuthn')->name('webauthn.add');
    Route::get('/webauthn/add', 'App\Http\Controllers\MfaController@addWebAuthn')->name('webauthn.adddevice');
    Route::get('/webauthn/test', 'App\Http\Controllers\MfaController@performWebAuthn')->name('webauthn.test');
    Route::get('/sms', 'App\Http\Controllers\MfaController@showSms')->name('sms');
    Route::get('/sms/test', 'App\Http\Controllers\MfaController@performSms')->name('sms.test');
    Route::get('/trusted', 'App\Http\Controllers\MfaController@showTrusted')->name('trusted');
    Route::delete('/trusted', 'App\Http\Controllers\MfaController@deleteTrusted')->name('trusted.delete');
    Route::delete('/trusted/{device}', 'App\Http\Controllers\MfaController@deleteTrusted')->name('trusted.delete.one');
});