<?php

namespace App\Services;

use App\Enums\CompletionStatus;
use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;
use App\Models\Region;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Computes the logbook's derived numbers. None of these are ever stored.
 *
 * - A region's "done" counts its countries whose status is completed; its total counts all its countries.
 * - The world figure sums every region. Each country belongs to exactly one region, so none is counted twice.
 * - Places by type: completed + partial = sum. Capitals are counted under their own type (City).
 */
class LogbookProgress
{
    /**
     * @param  Collection<int, Region>  $regions  Regions with their `countries` loaded.
     * @return array{
     *     world: array{done: int, total: int, percent: int},
     *     regions: list<array{region: Region, done: int, total: int}>,
     * }
     */
    public function countryProgress(Collection $regions): array
    {
        $regionTallies = $regions->map(fn (Region $region): array => [
            'region' => $region,
            'done' => $region->countries->filter(fn (Country $country): bool => $country->status === CompletionStatus::Completed)->count(),
            'total' => $region->countries->count(),
        ])->values()->all();

        $worldDone = array_sum(array_column($regionTallies, 'done'));
        $worldTotal = array_sum(array_column($regionTallies, 'total'));

        return [
            'world' => [
                'done' => $worldDone,
                'total' => $worldTotal,
                'percent' => $worldTotal > 0 ? (int) round($worldDone / $worldTotal * 100) : 0,
            ],
            'regions' => $regionTallies,
        ];
    }

    /**
     * @param  Collection<int, PlaceType>  $placeTypes
     * @return list<array{placeType: PlaceType, completed: int, partial: int, sum: int}>
     */
    public function placesByType(Collection $placeTypes): array
    {
        $counts = Place::query()
            ->selectRaw('place_type_id, status, count(*) as aggregate')
            ->whereIn('status', [CompletionStatus::Completed, CompletionStatus::Partial])
            ->groupBy('place_type_id', 'status')
            ->toBase()
            ->get()
            ->mapWithKeys(fn (object $row): array => ["{$row->place_type_id}:{$row->status}" => (int) $row->aggregate]);

        return $placeTypes->map(function (PlaceType $placeType) use ($counts): array {
            $completed = $counts->get("{$placeType->id}:".CompletionStatus::Completed->value, 0);
            $partial = $counts->get("{$placeType->id}:".CompletionStatus::Partial->value, 0);

            return [
                'placeType' => $placeType,
                'completed' => $completed,
                'partial' => $partial,
                'sum' => $completed + $partial,
            ];
        })->values()->all();
    }

    /**
     * Country names are compared ignoring case and outer spaces.
     */
    public static function normalizeName(string $name): string
    {
        return Str::lower(trim($name));
    }
}
