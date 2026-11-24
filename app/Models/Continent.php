<?php

namespace App\Models;

use Database\Factories\ContinentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'position'])]
class Continent extends Model
{
    /** @use HasFactory<ContinentFactory> */
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
