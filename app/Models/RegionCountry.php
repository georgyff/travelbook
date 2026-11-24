<?php

namespace App\Models;

use Database\Factories\RegionCountryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One country name listed in a region. Matched to a logbook country by name, case-insensitively.
 */
#[Fillable(['region_id', 'name', 'position'])]
class RegionCountry extends Model
{
    /** @use HasFactory<RegionCountryFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Region, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
