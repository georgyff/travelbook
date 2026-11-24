<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderRequest;
use App\Models\Continent;
use App\Models\Country;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * The drag-and-drop order of the countries within one continent.
 */
class CountryOrderController extends Controller
{
    public function update(ReorderRequest $request, Continent $continent): Response
    {
        $ids = $request->orderedIdsMatching($continent->countries()->pluck('id'));

        DB::transaction(function () use ($ids): void {
            foreach ($ids as $index => $id) {
                Country::query()->whereKey($id)->update(['position' => $index + 1]);
            }
        });

        return response()->noContent();
    }
}
