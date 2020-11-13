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
Route::delete('/alert/{alert}', 'BaseController@acknowledgeAlert')->name('acknowledge');

Route::get('/about', 'BaseController@about')->name('intro.about');
Route::get('/nebula', 'BaseController@nebula')->name('intro.nebula');
Route::get('/new', 'BaseController@newToColonia')->name('intro.new');
Route::get('/regions', 'BaseController@regions')->name('intro.regions');
Route::get('/story', 'BaseController@story')->name('intro.story');
Route::get('/icons', 'BaseController@icons')->name('intro.icons');
Route::get('/radio', 'ArticleController@index')->name('radio');
Route::get('/radio/{sequence}', 'ArticleController@show')->name('radio.sequence');

Route::get('/distances', 'DistancesController@index')->name('distances');

Route::get('/history', 'HistoryController@index')->name('history');
Route::get('/history/create', 'HistoryController@create')->name('history.create');
Route::post('/history', 'HistoryController@store')->name('history.store');

Route::get('/history/trends', 'HistoryController@trends')->name('history.trends');
Route::get('/history/space', 'HistoryController@spaceTrends')->name('history.spacetrends');

Route::get('/systems/eddb/{eddb}', 'SystemController@eddb');
Route::resource('systems', 'SystemController');
Route::get('/systems/{system}/history', [
    'as' => 'systems.showhistory',
    'uses' => 'SystemController@showhistory'
]);
Route::get('/systems/{system}/happiness', [
    'as' => 'systems.showhappiness',
    'uses' => 'SystemController@showhappiness'
]);
Route::get('/systems/{system}/editreport', [
    'as' => 'systems.editreport',
    'uses' => 'SystemController@editreport'
]);
Route::put('/systems/{system}/updatereport', [
    'as' => 'systems.updatereport',
    'uses' => 'SystemController@updatereport'
]);


Route::get('/factions/ethos', [
    'as' => 'factions.ethos',
    'uses' => 'FactionController@ethos'
]);
Route::resource('factions', 'FactionController');
Route::get('/factions/{faction}/history', [
    'as' => 'factions.showhistory',
    'uses' => 'FactionController@showhistory'
]);
Route::get('/factions/{faction}/happiness', [
    'as' => 'factions.showhappiness',
    'uses' => 'FactionController@showhappiness'
]);


Route::get('/users', [
    'as' => 'users.index',
    'uses' => 'UserController@index'
]);
Route::post('/users', [
    'as' => 'users.update',
    'uses' => 'UserController@update'
]);

Route::get('/stations/eddb/{eddb}', 'StationController@eddb');
Route::get('/stations/{station}/trade', [
    'as' => 'stations.showtrade',
    'uses' => 'StationController@trade'
]);
Route::get('/stations/{station}/trade/{commodity}', [
    'as' => 'stations.showtradehistory',
    'uses' => 'StationController@tradeHistory'
]);
Route::get('/stations/{station}/outfitting', [
    'as' => 'stations.showoutfitting',
    'uses' => 'StationController@outfitting'
]);
Route::get('/stations/{station}/shipyard', [
    'as' => 'stations.showshipyard',
    'uses' => 'StationController@shipyard'
]);
Route::get('/stations/{station}/outfitting/current', [
    'as' => 'stations.showoutfitting.current',
    'uses' => 'StationController@outfittingCurrent'
]);
Route::resource('stations', 'StationController');

Route::resource('megaships', 'MegashipController');
Route::resource('installations', 'InstallationController');
Route::resource('sites', 'SiteController');

Route::resource('engineers', 'EngineerController');

Route::resource('projects', 'ProjectController');

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
Route::get('/reports/reach/log', [
    'as' => 'reports.reach.log',
    'uses' => 'ReportController@reachLog'
]);
Route::get('/reports/reach/happy', [
    'as' => 'reports.reach.happy',
    'uses' => 'ReportController@reachHappy'
]);
Route::get('/reports/states', [
    'as' => 'reports.states',
    'uses' => 'ReportController@states'
]);

Route::get('/trade', [
    'as' => 'trade',
    'uses' => 'TradeController@index'
]);

Route::post('/trade', [
    'as' => 'trade.search',
    'uses' => 'TradeController@index'
]);

Route::get('/reserves/{commodity}/history', [
    'as' => 'reserves.commodity.history',
    'uses' => 'TradeController@commodityHistory'
]);
Route::get('/reserves/{commodity}/pricehistory', [
    'as' => 'reserves.commodity.pricehistory',
    'uses' => 'TradeController@commodityPriceHistory'
]);

Route::get('/reserves/{commodity}/{station}', [
    'as' => 'reserves.commodity.reference',
    'uses' => 'TradeController@commodityWithReference'
]);

Route::get('/reserves/{commodity}', [
    'as' => 'reserves.commodity',
    'uses' => 'TradeController@commodity'
]);

Route::get('/reserves', [
    'as' => 'reserves',
    'uses' => 'TradeController@reserves'
]);

Route::get('/specialisation', [
    'as' => 'specialisation',
    'uses' => 'TradeController@specialisation'
]);

Route::get('/specialisation/{economy}', [
    'as' => 'specialisation.economy',
    'uses' => 'TradeController@specialisationEconomy'
]);

Route::get('/logistics', [
    'as' => 'logistics',
    'uses' => 'LogisticsController@form'
]);
Route::post('/logistics/configure', [
    'as' => 'logistics.configure',
    'uses' => 'LogisticsController@configure'
]);
Route::get('/logistics/{config}', [
    'as' => 'logistics.report',
    'uses' => 'LogisticsController@report'
]);


Route::get('/effects', [
    'as' => 'effects',
    'uses' => 'TradeController@effects'
]);

Route::get('/effects/c/{commodity}', [
    'as' => 'effects.commodity',
    'uses' => 'TradeController@effectsCommodity'
]);

Route::get('/effects/s/{state}', [
    'as' => 'effects.state',
    'uses' => 'TradeController@effectsState'
]);

Route::get('/outfitting/current', [
    'as' => 'outfitting.current',
    'uses' => 'OutfittingController@current'
]);
Route::get('/outfitting/shipyard/{ship}', [
    'as' => 'outfitting.shipyard.ship',
    'uses' => 'OutfittingController@ship'
]);
Route::get('/outfitting/shipyard', [
    'as' => 'outfitting.shipyard',
    'uses' => 'OutfittingController@shipyard'
]);
Route::get('/outfitting/{moduletype}/{module}', [
    'as' => 'outfitting.module',
    'uses' => 'OutfittingController@module'
]);
Route::get('/outfitting/{moduletype}', [
    'as' => 'outfitting.moduletype',
    'uses' => 'OutfittingController@moduletype'
]);
Route::get('/outfitting', [
    'as' => 'outfitting',
    'uses' => 'OutfittingController@index'
]);

Route::get('/visit', [
    'as' => 'visit',
    'uses' => 'VisitController@index'
]);
