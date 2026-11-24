<?php

namespace App\Models;

use App\Enums\CompletionStatus;
use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['continent_id', 'name', 'status', 'history', 'position'])]
class Country extends Model
{
    /** @use HasFactory<CountryFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'not_completed',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CompletionStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Continent, $this>
     */
    public function continent(): BelongsTo
    {
        return $this->belongsTo(Continent::class);
    }

    /**
     * Places in the order they were recorded.
     *
     * @return HasMany<Place, $this>
     */
    public function places(): HasMany
    {
        return $this->hasMany(Place::class)->orderBy('id');
    }
}
