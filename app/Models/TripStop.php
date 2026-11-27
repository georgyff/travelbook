<?php

namespace App\Models;

use Database\Factories\TripStopFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One row of a trip's itinerary. Every cell is typed in by hand; nothing is looked up.
 *
 * @property Carbon|null $visited_on
 */
#[Fillable(['trip_id', 'position', 'visited_on', 'city', 'transport_cost', 'hotel_cost', 'times', 'country'])]
class TripStop extends Model
{
    /** @use HasFactory<TripStopFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visited_on' => 'date',
            'transport_cost' => 'float',
            'hotel_cost' => 'float',
        ];
    }

    /**
     * @return BelongsTo<Trip, $this>
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
