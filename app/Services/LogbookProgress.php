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
 * - A region's "done" counts its countries whose logbook status is completed. Names that aren't
 *   in the logbook count toward the total as not completed.
 * - The world figure counts each distinct country named in any region once, even if it appears
 *   in several regions.
 * - Places by type: completed + partial = sum. Capitals are counted under their own type (City).
 */
class LogbookProgress
{
    /**
     * @param  Collection<int, Region>  $regions  Regions with their `members` loaded.
     * @return array{
     *     world: array{done: int, total: int, percent: int},
     *     regions: list<array{region: Region, done: int, total: int}>,
     * }
     */
    public function countryProgress(Collection $regions): array
    {
        $completedNames = Country::query()
            ->where('status', CompletionStatus::Completed)
            ->pluck('name')
            ->map(fn (string $name): string => self::normalizeName($name))
            ->flip();

        $isCompleted = fn (string $name): bool => $completedNames->has(self::normalizeName($name));

        $regionTallies = $regions->map(function (Region $region) use ($isCompleted): array {
            $names = $region->members->pluck('name');

            return [
                'region' => $region,
                'done' => $names->filter($isCompleted)->count(),
                'total' => $names->count(),
            ];
        })->values()->all();

        $worldNames = $regions
            ->flatMap(fn (Region $region): Collection => $region->members->pluck('name'))
            ->unique(fn (string $name): string => self::normalizeName($name));

        $worldDone = $worldNames->filter($isCompleted)->count();
        $worldTotal = $worldNames->count();

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
     * Region members are matched to logbook countries by name, ignoring case and outer spaces.
     */
    public static function normalizeName(string $name): string
    {
        return Str::lower(trim($name));
    }
}
