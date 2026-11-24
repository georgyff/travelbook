<?php

namespace App\Models;

use Database\Factories\RegionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A user-defined list of country names. Names may refer to countries not yet in the logbook.
 */
#[Fillable(['name', 'position'])]
class Region extends Model
{
    /** @use HasFactory<RegionFactory> */
    use HasFactory;

    /**
     * @return HasMany<RegionCountry, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(RegionCountry::class)->orderBy('position')->orderBy('id');
    }
}
