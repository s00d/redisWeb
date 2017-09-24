<?php

use Illuminate\Http\Request;

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
    return $request->user();
});

Route::get('/getList', 'DataController@getList');

Route::get('/getItem', 'DataController@getItem');
Route::get('/getInfo', 'DataController@getInfo');

Route::get('/export', 'DataController@export');

Route::post('/setTTL', 'DataController@setTTL');
Route::post('/setName', 'DataController@setName');
Route::post('/saveItem', 'DataController@saveItem');

Route::post('/import', 'DataController@import');

Route::delete('/removeItem', 'DataController@removeItem');