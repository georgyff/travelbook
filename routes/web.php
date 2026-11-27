<?php

use App\Http\Controllers\CountryController;
use App\Http\Controllers\CountryHistoryController;
use App\Http\Controllers\CountryOrderController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\PlaceOrderController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\RegionOrderController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\TripItineraryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LogbookController::class, 'index'])->name('logbook');

Route::resource('regions', RegionController::class)->except(['index', 'show']);
Route::put('region-order', [RegionOrderController::class, 'update'])->name('region-order.update');
Route::put('regions/{region}/country-order', [CountryOrderController::class, 'update'])->name('regions.country-order.update');

Route::resource('countries', CountryController::class)->except(['index', 'show']);
Route::get('countries/{country}', [LogbookController::class, 'show'])->name('countries.show');
Route::get('countries/{country}/history/edit', [CountryHistoryController::class, 'edit'])->name('countries.history.edit');
Route::put('countries/{country}/history', [CountryHistoryController::class, 'update'])->name('countries.history.update');

Route::resource('countries.places', PlaceController::class)->shallow()->except(['index', 'show']);
Route::put('countries/{country}/place-types/{placeType}/place-order', [PlaceOrderController::class, 'update'])->name('countries.place-order.update');
Route::put('countries/{country}/capital-order', [PlaceOrderController::class, 'updateCapitals'])->name('countries.capital-order.update');

Route::resource('trips', TripController::class);
Route::get('trips/{trip}/itinerary/edit', [TripItineraryController::class, 'edit'])->name('trips.itinerary.edit');
Route::put('trips/{trip}/itinerary', [TripItineraryController::class, 'update'])->name('trips.itinerary.update');
