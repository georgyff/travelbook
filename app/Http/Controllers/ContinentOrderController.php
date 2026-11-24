<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderRequest;
use App\Models\Continent;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * The order of the continent groups in the country index.
 */
class ContinentOrderController extends Controller
{
    public function update(ReorderRequest $request): Response
    {
        $ids = $request->orderedIdsMatching(Continent::query()->pluck('id'));

        DB::transaction(function () use ($ids): void {
            foreach ($ids as $index => $id) {
                Continent::query()->whereKey($id)->update(['position' => $index + 1]);
            }
        });

        return response()->noContent();
    }
}
