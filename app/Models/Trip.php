<?php

namespace App\Models;

use App\Enums\TripStatus;
use App\Enums\TripType;
use App\Services\LogbookProgress;
use Database\Factories\TripFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * A trip. Days are entered by hand (not worked out from the dates). The total cost, average spend
 * per day and the regions the trip went to are derived here and never stored.
 *
 * @property Carbon|null $starts_on
 * @property Carbon|null $ends_on
 */
#[Fillable(['name', 'status', 'type', 'places_visited', 'starts_on', 'ends_on', 'days', 'walked_steps', 'walked_km', 'pictures', 'cost_travel', 'cost_housing', 'cost_in_travel'])]
class Trip extends Model
{
    /** @use HasFactory<TripFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'idea',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TripStatus::class,
            'type' => TripType::class,
            'places_visited' => 'array',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'days' => 'integer',
            'walked_steps' => 'integer',
            'walked_km' => 'float',
            'pictures' => 'integer',
            'cost_travel' => 'float',
            'cost_housing' => 'float',
            'cost_in_travel' => 'float',
        ];
    }

    /**
     * Logbook countries in the order they were travelled.
     *
     * @return BelongsToMany<Country, $this>
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class)->withPivot('position')->orderByPivot('position');
    }

    /**
     * The places visited, each with a link to its Logbook place when one of the trip's countries has
     * a place of that name (ignoring case). Needs countries.places loaded.
     *
     * @return list<array{name: string, url: string|null}>
     */
    public function placesVisitedWithLinks(): array
    {
        $logbookPlaces = $this->countries
            ->flatMap(fn (Country $country) => $country->places)
            ->keyBy(fn (Place $place): string => LogbookProgress::normalizeName($place->name));

        return array_map(function (string $name) use ($logbookPlaces): array {
            $place = $logbookPlaces->get(LogbookProgress::normalizeName($name));

            return [
                'name' => $name,
                'url' => $place ? route('countries.show', [$place->country_id, 'place' => $place->id]) : null,
            ];
        }, $this->places_visited ?? []);
    }

    /**
     * The rows of the itinerary, in the order entered.
     *
     * @return HasMany<TripStop, $this>
     */
    public function stops(): HasMany
    {
        return $this->hasMany(TripStop::class)->orderBy('position')->orderBy('id');
    }

    /**
     * Inter-country/city travel + housing + in-travel, or null when none of them is filled in.
     */
    public function totalCost(): ?float
    {
        $parts = array_filter([$this->cost_travel, $this->cost_housing, $this->cost_in_travel], fn (?float $part): bool => $part !== null);

        return $parts === [] ? null : array_sum($parts);
    }

    /**
     * AVG EUR per day = in-travel ÷ days. Null until both are filled in (and days isn't zero).
     */
    public function averagePerDay(): ?float
    {
        if ($this->cost_in_travel === null || ! $this->days) {
            return null;
        }

        return $this->cost_in_travel / $this->days;
    }

    /**
     * The regions of the trip's countries, each once, in the Logbook's region order.
     *
     * @return Collection<int, Region>
     */
    public function regions(): Collection
    {
        return $this->countries->pluck('region')->filter()->unique('id')->sortBy([['position', 'asc'], ['id', 'asc']])->values();
    }

    /**
     * The dates as a compact range: "3 → 17 May 2026", "28 Apr → 5 May 2026", "28 Dec 2025 → 4 Jan 2026".
     */
    public function dateRange(): ?string
    {
        $start = $this->starts_on;
        $end = $this->ends_on;

        if (! $start || ! $end) {
            return match (true) {
                (bool) $start => 'from '.$start->format('j M Y'),
                (bool) $end => 'until '.$end->format('j M Y'),
                default => null,
            };
        }

        if ($start->isSameDay($end)) {
            return $start->format('j M Y');
        }

        $startFormat = match (true) {
            $start->isSameMonth($end) => 'j',
            $start->isSameYear($end) => 'j M',
            default => 'j M Y',
        };

        return $start->format($startFormat).' → '.$end->format('j M Y');
    }
}
