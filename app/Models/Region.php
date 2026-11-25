<?php

namespace App\Models;

use Database\Factories\RegionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A group of countries. It is both a group in the country index and a row in the Regions progress list.
 */
#[Fillable(['name', 'position'])]
class Region extends Model
{
    /** @use HasFactory<RegionFactory> */
    use HasFactory;

    /**
     * Countries in the owner's drag-and-drop order.
     *
     * @return HasMany<Country, $this>
     */
    public function countries(): HasMany
    {
        return $this->hasMany(Country::class)->orderBy('position')->orderBy('id');
    }
}
