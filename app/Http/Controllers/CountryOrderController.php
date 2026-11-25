<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderRequest;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * The drag-and-drop order of the countries within one region.
 */
class CountryOrderController extends Controller
{
    public function update(ReorderRequest $request, Region $region): Response
    {
        $ids = $request->orderedIdsMatching($region->countries()->pluck('id'));

        DB::transaction(function () use ($ids): void {
            foreach ($ids as $index => $id) {
                Country::query()->whereKey($id)->update(['position' => $index + 1]);
            }
        });

        return response()->noContent();
    }
}
