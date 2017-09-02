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

Route::get('/about', 'BaseController@about')->name('intro.about');
Route::get('/new', 'BaseController@newToColonia')->name('intro.new');

Route::get('/distances', 'DistancesController@index')->name('distances');

Route::get('/history', 'HistoryController@index')->name('history');
Route::get('/history/create', 'HistoryController@create')->name('history.create');
Route::post('/history', 'HistoryController@store')->name('history.store');

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

Route::get('/reports', [
    'as' => 'reports',
    'uses' => 'ReportController@index'
]);
Route::get('/reports/traffic', [
    'as' => 'reports.traffic',
    'uses' => 'ReportController@traffic'
]);
Route::get('/reports/crimes', [
    'as' => 'reports.crimes',
    'uses' => 'ReportController@crimes'
]);
Route::get('/reports/bounties', [
    'as' => 'reports.bounties',
    'uses' => 'ReportController@bounties'
]);
Route::get('/reports/control', [
    'as' => 'reports.control',
    'uses' => 'ReportController@control'
]);
Route::get('/reports/reach', [
    'as' => 'reports.reach',
    'uses' => 'ReportController@reach'
]);

Route::get('/trade', [
    'as' => 'trade',
    'uses' => 'TradeController@index'
]);

Route::post('/trade', [
    'as' => 'trade.search',
    'uses' => 'TradeController@index'
]);


