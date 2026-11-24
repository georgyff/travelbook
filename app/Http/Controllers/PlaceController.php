<?php

namespace App\Http\Controllers;

use App\Enums\CompletionStatus;
use App\Http\Requests\PlaceRequest;
use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlaceController extends Controller
{
    public function create(Country $country): View
    {
        return view('logbook.forms.place', [
            'country' => $country,
            'place' => new Place([
                'status' => CompletionStatus::NotCompleted,
                'is_capital' => false,
                'facts' => [],
            ]),
            'placeTypes' => PlaceType::query()->orderBy('position')->orderBy('id')->get(),
            'currentCapital' => $country->places()->where('is_capital', true)->first(),
        ]);
    }

    public function store(PlaceRequest $request, Country $country): JsonResponse|RedirectResponse
    {
        $place = DB::transaction(function () use ($request, $country): Place {
            $place = $country->places()->create($request->validated());
            $this->keepSingleCapital($place);

            return $place;
        });

        return $this->finishedAt(route('countries.show', [$country, 'place' => $place->id]));
    }

    public function edit(Place $place): View
    {
        return view('logbook.forms.place', [
            'country' => $place->country,
            'place' => $place,
            'placeTypes' => PlaceType::query()->orderBy('position')->orderBy('id')->get(),
            'currentCapital' => $place->country->places()->where('is_capital', true)->whereKeyNot($place->id)->first(),
        ]);
    }

    public function update(PlaceRequest $request, Place $place): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($request, $place): void {
            $place->update($request->validated());
            $this->keepSingleCapital($place);
        });

        return $this->finishedAt(route('countries.show', [$place->country_id, 'place' => $place->id]));
    }

    public function destroy(Place $place): JsonResponse|RedirectResponse
    {
        $place->delete();

        return $this->finishedAt(route('countries.show', $place->country_id));
    }

    /**
     * A country has one capital: marking a place as capital clears the flag on the others.
     */
    private function keepSingleCapital(Place $place): void
    {
        if (! $place->is_capital) {
            return;
        }

        Place::query()
            ->where('country_id', $place->country_id)
            ->whereKeyNot($place->id)
            ->where('is_capital', true)
            ->update(['is_capital' => false]);
    }
}
