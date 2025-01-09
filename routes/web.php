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
})->middleware(['auth', 'model'])->name('home');

Route::get('/myinfo', function() {
    return session(Auth::guard()->getName() . "_ac");
})->middleware(['auth']);

Route::get('/login', 'App\Http\Controllers\Auth\LoginController@login')->name('login');
Route::get('/stepup/{method}', 'App\Http\Controllers\Auth\LoginController@stepup')->name('stepup');
Route::get('/logout', 'App\Http\Controllers\Auth\LoginController@logout')->name('logout');
Route::post('/logout', 'App\Http\Controllers\Auth\LoginController@logout')->name('logout');

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

Route::name('ext.')
->prefix('ext')
->group(function() {
    Route::get('/', 'App\Http\Controllers\UserExtController@showOverview')->name('home');
    Route::get('/login/{client}', 'App\Http\Controllers\UserExtController@loginRemote')->name('login.ext'); 
    Route::get('/login', 'App\Http\Controllers\UserExtController@loginLocal')->name('login');
    Route::get('/confirm/{client}', 'App\Http\Controllers\UserExtController@confirmIdentity')->name('confirm');
    Route::post('/add/{client}', 'App\Http\Controllers\UserExtController@addIdentity')->name('add');
    Route::post('/remove/{client}', 'App\Http\Controllers\UserExtController@removeIdentity')->name('remove');    
    Route::get('/ssoinfo', 'App\Http\Controllers\UserExtController@ssoInfo')->name('ssoinfo');
});

Route::name('reset.')
->prefix('reset')
->group(function() {
   Route::get('/', 'App\Http\Controllers\ResetController@showMethods')->name('home');
   Route::get('/search', 'App\Http\Controllers\ResetController@showSearch')->name('find');
   Route::post('/search', 'App\Http\Controllers\ResetController@findUser')->name('search');
   Route::get('/methods', 'App\Http\Controllers\ResetController@showMethods')->name('methods');
   Route::get('/verify/{method}', 'App\Http\Controllers\ResetController@verifyUser')->name('verify');
   Route::get('/unverified', 'App\Http\Controllers\ResetController@warnUnverified')->name('unverified');
   Route::get('/inquiry', 'App\Http\Controllers\ResetController@showInquiry')->name('inquiry');
   Route::post('/merge', 'App\Http\Controllers\ResetController@mergeData')->name('merge');
   Route::get('/idcheck', 'App\Http\Controllers\ResetController@checkIdentity')->name('idcheck');
   Route::get('/failed', 'App\Http\Controllers\ResetController@checkFailed')->name('failed');
   Route::get('/restart', 'App\Http\Controllers\ResetController@cleanRemote')->name('restart');
   Route::get('/password', 'App\Http\Controllers\ResetController@showPasswordForm')->name('password');
   Route::post('/change', 'App\Http\Controllers\ResetController@changePassword')->name('change');
});

Route::name('challenge.')
->prefix('challenge')
->group(function() {
    Route::post('/phone', 'App\Http\Controllers\ChallengeController@createPhoneChallenge')->name('phone');
    Route::post('/email', 'App\Http\Controllers\ChallengeController@createMailChallenge')->name('email');
});

Route::name('password.')
->prefix('password')
->group(function() {
    Route::get('/', 'App\Http\Controllers\PasswordController@showPasswordForm')->name('home');
});
