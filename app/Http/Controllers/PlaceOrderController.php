<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderRequest;
use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * The drag-and-drop order of a country's places within one group: its capitals, or its other
 * places of one type. Positions count within the group.
 */
class PlaceOrderController extends Controller
{
    public function update(ReorderRequest $request, Country $country, PlaceType $placeType): Response
    {
        $this->savePositions($request->orderedIdsMatching(
            $country->places()->where('place_type_id', $placeType->id)->where('is_capital', false)->pluck('id'),
        ));

        return response()->noContent();
    }

    public function updateCapitals(ReorderRequest $request, Country $country): Response
    {
        $this->savePositions($request->orderedIdsMatching(
            $country->places()->where('is_capital', true)->pluck('id'),
        ));

        return response()->noContent();
    }

    /**
     * @param  list<int>  $ids
     */
    private function savePositions(array $ids): void
    {
        DB::transaction(function () use ($ids): void {
            foreach ($ids as $index => $id) {
                Place::query()->whereKey($id)->update(['position' => $index + 1]);
            }
        });
    }
}
