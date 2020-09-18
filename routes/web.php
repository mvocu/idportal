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

Route::get('/activate/token/{id}/{token?}', 'Auth\ActivateController@showActivateForm')->name('activate.token');
// posts to
Route::post('/activate/activate', 'Auth\ActivateController@activate')->name('activate.activate');

Route::get('/password/change/{target?}', 'Auth\PasswordController@showPasswordForm')->name('password.change');
// posts to
Route::post('/password/change/{target?}', 'Auth\PasswordController@changePassword')->name('password.change.update');

Route::get('/home', 'HomeController@index')->name('home');

Route::name('ext.account.')
    ->prefix('ext/account')
    ->group(function() {
        Route::get('/ask/{user}/{source}', 'UserExtController@showAddUserExtForm')->name('ask');
        Route::post('/add/{user}/{source}', 'UserExtController@addUserExt')->name('add');
        Route::get('/show/{user}/{source}', 'UserExtController@showUserExtForm')->name('show');
        Route::post('/modify/{user_ext}/{action}', 'UserExtController@modifyUserExt')->name('modify');
        Route::get('/remove/{user_ext}', 'UserExtController@removeUserExt')->name('remove');
    });

Route::get('/register/oidc/{client}', 'Auth\OidcRegisterController@show')->name('register.oidc');

Route::post('/register/oidc/{client}', 'Auth\OidcRegisterController@register')->name('register.oidc.create');

Route::post('/add/oidc/{client}', 'Auth\OidcRegisterController@add')->name('register.oidc.add');

Route::get('/login/oidc/{client}', 'Auth\LoginController@loginOidc')->name('login.oidc');

Route::get('/logout/oidc/{client}', function($client) { 
        Auth::guard($client)->logout();
        return back();
})->name('oidc.logout');

Route::get('/consent/ask', 'ConsentController@showConsentForm')->name('consent.ask');

Route::post('/consent/set', 'ConsentController@setConsent')->name('consent.set');

Route::name('admin.')
    ->prefix('admin')
    ->namespace('Admin')
    ->group(function() {
        Route::get('/user/list', 'UserController@listUsers')->name('user.list');
        Route::post('/user/list', 'UserController@listUsers')->name('user.list.search');
        Route::get('/user/show/{user}', 'UserController@showUser')->name('user.show');
        Route::get('/userext/list', 'UserExtController@listUsers')->name('userext.list'); 
        Route::get('/userext/list/{id}', 'UserExtController@listUsers')->name('userext.list.source');
        Route::post('/userext/list', 'UserExtController@listUsers')->name('userext.list.search');
        Route::post('/userext/list/{id}', 'UserExtController@listUsers')->name('userext.list.search.source');
        Route::get('/userext/show/{user}', 'UserExtController@showUser')->name('userext.show');
        Route::post('/userext/synchronize/', 'UserExtController@synchronize')->name('userext.synchronize');
        Route::get('/userext/notify/{user}', 'UserExtController@notify')->name('userext.notify');
    });
    
Route::get('/mojeid/info', function() { return response()->json([
    'https://cas.mestouvaly.cz/cas/login?client_name=OidcClient',
    'https://cas.mestouvaly.cz/cas/login/MojeID', 
    'https://cas.mestouvaly.cz/register/oidc/MojeID',
    'https://cas.mestouvaly.cz/password/oidc/MojeID',
    'https://cas.mestouvaly.cz/add/oidc/MojeID',
    'https://cas.mestouvaly.cz/login/oidc/MojeID',
    'https://localhost:8000/register/oidc/MojeID',
    'https://localhost:8000/password/oidc/MojeID',
    'https://localhost:8000/add/oidc/MojeID',
    'https://localhost:8000/login/oidc/MojeID'
]); } );
