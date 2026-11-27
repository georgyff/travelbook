<?php

namespace App\Enums;

use App\Models\Trip;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The columns of the trips table. Every column can sort the table.
 */
enum TripColumn: string
{
    case Name = 'name';
    case Status = 'status';
    case Dates = 'dates';
    case Countries = 'countries';
    case Places = 'places';
    case Continent = 'continent';
    case Days = 'days';
    case Steps = 'steps';
    case Km = 'km';
    case Cost = 'cost';
    case Type = 'type';
    case Pictures = 'pictures';

    public function label(): string
    {
        return match ($this) {
            self::Name => 'Trip',
            self::Km => 'Km',
            self::Places => 'Places visited',
            default => ucfirst($this->value),
        };
    }

    /**
     * Figures are right-aligned so their digits line up.
     */
    public function isNumeric(): bool
    {
        return in_array($this, [self::Days, self::Steps, self::Km, self::Cost, self::Pictures], true);
    }

    /**
     * The value a trip sorts by in this column, or null when it has none.
     */
    public function sortValue(Trip $trip): string|int|float|null
    {
        return match ($this) {
            self::Name => Str::lower($trip->name),
            self::Status => $trip->status->rank(),
            self::Dates => ($trip->starts_on ?? $trip->ends_on)?->getTimestamp(),
            self::Countries => $trip->countries->isEmpty() ? null : Str::lower($trip->countries->pluck('name')->join(', ')),
            self::Places => empty($trip->places_visited) ? null : Str::lower(implode(', ', $trip->places_visited)),
            self::Continent => ($regions = $trip->regions())->isEmpty() ? null : Str::lower($regions->pluck('name')->join(', ')),
            self::Days => $trip->days,
            self::Steps => $trip->walked_steps,
            self::Km => $trip->walked_km,
            self::Cost => $trip->totalCost(),
            self::Type => $trip->type?->value,
            self::Pictures => $trip->pictures,
        };
    }

    /**
     * Sort trips by this column. Trips without a value go last either way; ties fall back to the name.
     *
     * @param  Collection<int, Trip>  $trips
     * @return Collection<int, Trip>
     */
    public function sort(Collection $trips, bool $descending): Collection
    {
        [$withValue, $withoutValue] = $trips->partition(fn (Trip $trip): bool => $this->sortValue($trip) !== null);

        $sorted = $withValue->sort(function (Trip $a, Trip $b) use ($descending): int {
            $order = $this->sortValue($a) <=> $this->sortValue($b);

            return ($descending ? -$order : $order) ?: Str::lower($a->name) <=> Str::lower($b->name);
        });

        return $sorted->concat($withoutValue->sortBy(fn (Trip $trip): string => Str::lower($trip->name)))->values();
    }
}
