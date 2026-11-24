<?php

use App\Http\Controllers\ContinentController;
use App\Http\Controllers\ContinentOrderController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CountryHistoryController;
use App\Http\Controllers\CountryOrderController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\RegionOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LogbookController::class, 'index'])->name('logbook');

Route::resource('continents', ContinentController::class)->except(['index', 'show']);
Route::put('continent-order', [ContinentOrderController::class, 'update'])->name('continent-order.update');
Route::put('continents/{continent}/country-order', [CountryOrderController::class, 'update'])->name('continents.country-order.update');

Route::resource('countries', CountryController::class)->except(['index', 'show']);
Route::get('countries/{country}', [LogbookController::class, 'show'])->name('countries.show');
Route::get('countries/{country}/history/edit', [CountryHistoryController::class, 'edit'])->name('countries.history.edit');
Route::put('countries/{country}/history', [CountryHistoryController::class, 'update'])->name('countries.history.update');

Route::resource('countries.places', PlaceController::class)->shallow()->except(['index', 'show']);

Route::resource('regions', RegionController::class)->except(['index', 'show']);
Route::put('region-order', [RegionOrderController::class, 'update'])->name('region-order.update');
