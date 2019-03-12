<?php

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

Route::post('/password/send', 'Auth\ForgotPasswordController@sendResetCode')->name('password.send');
// ...redirects to...
Route::get('/password/token', 'Auth\ResetPasswordController@showResetForm')->name('password.token');

Route::get('/activate/request', 'Auth\ActivateController@showRequestForm')->name('activate.request');
// posts to
Route::post('/activate/send', 'Auth\ActivateController@sendActivationCode')->name('activate.send');
// redirects to
Route::get('/activate/token/{id}/{token?}', 'Auth\ActivateController@showTokenForm')->name('activate.token');
// posts to
Route::post('/activate/activate', 'Auth\ActivateController@activate')->name('activate.activate');
// redirects to /password/token

Route::get('/home', 'HomeController@index')->name('home');
