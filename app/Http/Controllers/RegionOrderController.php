<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderRequest;
use App\Models\Region;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * The order of the regions in the progress column.
 */
class RegionOrderController extends Controller
{
    public function update(ReorderRequest $request): Response
    {
        $ids = $request->orderedIdsMatching(Region::query()->pluck('id'));

        DB::transaction(function () use ($ids): void {
            foreach ($ids as $index => $id) {
                Region::query()->whereKey($id)->update(['position' => $index + 1]);
            }
        });

        return response()->noContent();
    }
}
