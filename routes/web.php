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

Route::get('/password/oidc/{client}', 'Auth\ResetPasswordController@showOidcForm')->name('password.oidc');
// posts to
Route::post('/password/oidc/{client}', 'Auth\ResetPasswordController@resetOidc')->name('password.oidc.update');

Route::get('/activate/request', 'Auth\ActivateController@showRequestForm')->name('activate.request');
// posts to
Route::post('/activate/send', 'Auth\ActivateController@sendActivationCode')->name('activate.send');
// redirects to
Route::get('/activate/token/{id}/{token?}', 'Auth\ActivateController@showActivateForm')->name('activate.token');
// posts to
Route::post('/activate/activate', 'Auth\ActivateController@activate')->name('activate.activate');

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/ext/account/ask/{user}/{source}', 'UserExtController@showAddUserExtForm')->name('ext.account.ask');

Route::post('/ext/account/add/{user}/{source}', 'UserExtController@addUserExt')->name('ext.account.add');

Route::get('/ext/account/show/{user}/{source}', 'UserExtController@showUserExtForm')->name('ext.account.show');

Route::post('/ext/account/modify/{user_ext}/{action}', 'UserExtController@modifyUserExt')->name('ext.account.modify');

Route::get('/ext/account/remove/{user_ext}', 'UserExtController@removeUserExt')->name('remove.oidc');

Route::get('/register/oidc/{client}', 'Auth\OidcRegisterController@show')->name('register.oidc');

Route::post('/register/oidc/{client}', 'Auth\OidcRegisterController@register')->name('register.oidc.create');

Route::post('/add/oidc/{client}', 'Auth\OidcRegisterController@add')->name('register.oidc.add');

Route::get('/logout/oidc/{client}', function($client) { 
        Auth::guard($client)->logout();
        return back();
})->name('oidc.logout');

Route::get('/mojeid/info', function() { return response()->json([
    'https://cas.mestouvaly.cz/cas/login?client_name=OidcClient',
    'https://cas.mestouvaly.cz/cas/login/MojeID', 
    'https://cas.mestouvaly.cz/register/oidc/MojeID',
    'https://cas.mestouvaly.cz/password/oidc/MojeID',
    'https://cas.mestouvaly.cz/add/oidc/MojeID',
    'https://localhost:8000/register/oidc/MojeID',
    'https://localhost:8000/password/oidc/MojeID',
    'https://localhost:8000/add/oidc/MojeID'
]); } );