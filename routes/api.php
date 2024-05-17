<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return json_encode($request->user());
});

Route::name('gauth')
    ->prefix('gauth')
    ->middleware('auth:api')
    ->group(function() {
        Route::post('/import', 'App\Http\Controllers\MfaController@importGauth')->name('import');
});