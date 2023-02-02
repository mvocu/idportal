<?php

use App\Http\Controllers\Admin\UserController;

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


Route::name('password.')
    ->prefix('password')
    ->group(function() {
        Route::post('/send', 'Auth\ForgotPasswordController@sendResetCode')->name('send');
        // ...redirects to...
        Route::get('/token', 'Auth\ResetPasswordController@showResetForm')->name('token');
        
        Route::get('/eidp/{client}', 'Auth\ResetPasswordController@showExtIdpForm')->name('eidp');
        // posts to
        Route::post('/eidp/{client}', 'Auth\ResetPasswordController@resetExtIdp')->name('eidp.update');

        Route::get('/change/{target?}', 'Auth\PasswordController@showPasswordForm')->name('change');
        // posts to
        Route::post('/change/{target?}', 'Auth\PasswordController@changePassword')->name('change.update');
        
    });
    

Route::get('/activate/token/{id}/{token?}', 'Auth\ActivateController@showActivateForm')->name('activate.token');
// posts to
Route::post('/activate/activate', 'Auth\ActivateController@activate')->name('activate.activate');


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

Route::get('/register/eidp/{client}', 'Auth\ExtIdpRegisterController@show')->name('register.eidp');

Route::post('/register/eidp/{client}', 'Auth\ExtIdpRegisterController@register')->name('register.eidp.create');

Route::post('/add/eidp/{client}', 'Auth\ExtIdpRegisterController@add')->name('register.eidp.add');

Route::get('/login/eidp/{client}', 'Auth\LoginController@loginExtIdp')->name('login.eidp');

Route::get('/logout/eidp/{client}', function($client) { 
        Auth::guard($client)->logout();
        return back();
})->name('eidp.logout');

Route::get('/consent/ask', 'ConsentController@showConsentForm')->name('consent.ask');

Route::post('/consent/set', 'ConsentController@setConsent')->name('consent.set');

Route::get('/voting/show', 'VotingCodeController@showCode')->name('voting.show');

Route::get('/voting/get', 'VotingCodeController@getCode')->name('voting.get');

Route::post('/voting/declare', 'VotingCodeController@declare')->name('voting.declare');

Route::name('admin.')
    ->prefix('admin')
    ->namespace('Admin')
    ->group(function() {
        Route::get('/user/list', 'UserController@listUsers')->name('user.list');
        Route::post('/user/list', 'UserController@listUsers')->name('user.list.search');
        Route::get('/user/new', 'UserController@newUser')->name('user.new');
        Route::post('/user/new', 'UserController@createUser')->name('user.create');
        Route::get('/user/code/{user}', 'UserController@showVotingCode')->name('user.show.code');
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
    'https://cas.mestouvaly.cz/register/eidp/MojeID',
    'https://cas.mestouvaly.cz/password/eidp/MojeID',
    'https://cas.mestouvaly.cz/add/eidp/MojeID',
    'https://cas.mestouvaly.cz/login/eidp/MojeID',
    'https://localhost:8000/register/eidp/MojeID',
    'https://localhost:8000/password/eidp/MojeID',
    'https://localhost:8000/add/eidp/MojeID',
    'https://localhost:8000/login/eidp/MojeID'
]); } );
