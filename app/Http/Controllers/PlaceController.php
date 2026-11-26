<?php

namespace App\Http\Controllers;

use App\Enums\CompletionStatus;
use App\Http\Requests\PlaceRequest;
use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * A country's places. A country may have any number of capitals (the Netherlands, Bolivia…).
 * Positions count within a place's group: the capitals, or the other places of its type.
 */
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
            'otherCapitals' => $country->places()->where('is_capital', true)->pluck('name'),
        ]);
    }

    public function store(PlaceRequest $request, Country $country): JsonResponse|RedirectResponse
    {
        $place = new Place($request->validated());
        $place->country()->associate($country);
        $place->position = $this->nextPosition($place);
        $place->save();

        return $this->finishedAt(route('countries.show', [$country, 'place' => $place->id]));
    }

    public function edit(Place $place): View
    {
        return view('logbook.forms.place', [
            'country' => $place->country,
            'place' => $place,
            'placeTypes' => PlaceType::query()->orderBy('position')->orderBy('id')->get(),
            'otherCapitals' => $place->country->places()->where('is_capital', true)->whereKeyNot($place->id)->pluck('name'),
        ]);
    }

    /**
     * Save the place. A place that moves to another group (made or unmade a capital, or given
     * another type while not a capital) goes to the end of that group.
     */
    public function update(PlaceRequest $request, Place $place): JsonResponse|RedirectResponse
    {
        $place->fill($request->validated());

        if ($place->isDirty('is_capital') || (! $place->is_capital && $place->isDirty('place_type_id'))) {
            $place->position = $this->nextPosition($place);
        }

        $place->save();

        return $this->finishedAt(route('countries.show', [$place->country_id, 'place' => $place->id]));
    }

    public function destroy(Place $place): JsonResponse|RedirectResponse
    {
        $place->delete();

        return $this->finishedAt(route('countries.show', $place->country_id));
    }

    /**
     * The position after the last place in the group this place belongs to.
     */
    private function nextPosition(Place $place): int
    {
        $group = Place::query()
            ->where('country_id', $place->country_id)
            ->where('is_capital', $place->is_capital)
            ->when($place->exists, fn ($query) => $query->whereKeyNot($place->id))
            ->unless($place->is_capital, fn ($query) => $query->where('place_type_id', $place->place_type_id));

        return (int) $group->max('position') + 1;
    }
}
