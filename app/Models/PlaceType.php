<?php

namespace App\Models;

use Database\Factories\PlaceTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A kind of place (City, Village, Nature, Monument…). Kept as data so more can be added later.
 */
#[Fillable(['name', 'plural_name', 'position'])]
class PlaceType extends Model
{
    /** @use HasFactory<PlaceTypeFactory> */
    use HasFactory;

    /**
     * @return HasMany<Place, $this>
     */
    public function places(): HasMany
    {
        return $this->hasMany(Place::class);
    }
}
