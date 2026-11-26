<?php

namespace App\Models;

use Database\Factories\PlaceTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A kind of place (City, Village, Nature, Monument…). Kept as data so more can be added later.
 */
#[Fillable(['name', 'plural_name', 'position'])]
class PlaceType extends Model
{
    /** @use HasFactory<PlaceTypeFactory> */
    use HasFactory;

    /**
     * Type names (singular or plural, lower case) that have their own icon.
     *
     * @var array<string, string>
     */
    private const ICONS = [
        'city' => 'city',
        'cities' => 'city',
        'town' => 'city',
        'towns' => 'city',
        'village' => 'village',
        'villages' => 'village',
        'nature' => 'nature',
        'monument' => 'monument',
        'monuments' => 'monument',
    ];

    /**
     * The icon for this type (see the place-icon component). Types without their own get a map pin.
     */
    public function icon(): string
    {
        foreach ([$this->name, $this->plural_name] as $label) {
            $icon = self::ICONS[Str::lower(trim((string) $label))] ?? null;

            if ($icon !== null) {
                return $icon;
            }
        }

        return 'place';
    }

    /**
     * @return HasMany<Place, $this>
     */
    public function places(): HasMany
    {
        return $this->hasMany(Place::class);
    }
}
