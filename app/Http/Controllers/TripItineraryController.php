<?php

namespace App\Http\Controllers;

use App\Http\Requests\TripItineraryRequest;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * A trip's itinerary, edited as a whole in one dialog.
 */
class TripItineraryController extends Controller
{
    public function edit(Trip $trip): View
    {
        return view('trips.itinerary-form', [
            'trip' => $trip->load('stops'),
        ]);
    }

    public function update(TripItineraryRequest $request, Trip $trip): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($request, $trip): void {
            $trip->stops()->delete();

            foreach ($request->rows() as $index => $row) {
                $trip->stops()->create([...$row, 'position' => $index + 1]);
            }
        });

        return $this->finishedAt(route('trips.show', $trip));
    }
}
