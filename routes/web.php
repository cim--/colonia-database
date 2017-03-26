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

Route::resource('systems', 'SystemController');
Route::get('/systems/{system}/editreport', [
    'as' => 'systems.editreport',
    'uses' => 'SystemController@editreport'
]);
Route::put('/systems/{system}/updatereport', [
    'as' => 'systems.updatereport',
    'uses' => 'SystemController@updatereport'
]);

Route::resource('factions', 'FactionController'); 



