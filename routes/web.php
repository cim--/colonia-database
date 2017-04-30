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

Auth::routes();

Route::get('/logout', 'Auth\LoginController@logout')->name('logout2');
Route::get('/', 'BaseController@index')->name('index');
Route::get('/progress', 'BaseController@progress')->name('progress');

Route::get('/distances', 'DistancesController@index')->name('distances');
Route::get('/history', 'HistoryController@index')->name('history');

Route::resource('systems', 'SystemController');
Route::get('/systems/{system}/history', [
    'as' => 'systems.showhistory',
    'uses' => 'SystemController@showhistory'
]);
Route::get('/systems/{system}/editreport', [
    'as' => 'systems.editreport',
    'uses' => 'SystemController@editreport'
]);
Route::put('/systems/{system}/updatereport', [
    'as' => 'systems.updatereport',
    'uses' => 'SystemController@updatereport'
]);

Route::resource('factions', 'FactionController');
Route::get('/factions/{faction}/history', [
    'as' => 'factions.showhistory',
    'uses' => 'FactionController@showhistory'
]);


Route::get('/users', [
    'as' => 'users.index',
    'uses' => 'UserController@index'
]);
Route::post('/users', [
    'as' => 'users.update',
    'uses' => 'UserController@update'
]);

Route::resource('stations', 'StationController');

Route::resource('missions', 'MissionController', ['except' => [
    'show', 'destroy' // for now
]]);

Route::get('/map', [
    'as' => 'map',
    'uses' => 'MapController@index'
]);



