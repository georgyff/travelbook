<?php

namespace App\Models;

use App\Enums\CompletionStatus;
use App\Services\CountryFlags;
use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['region_id', 'name', 'flag_code', 'status', 'history', 'position'])]
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
     * The country's flag as an emoji, or null when it has no flag code.
     */
    public function flagEmoji(): ?string
    {
        return $this->flag_code ? (new CountryFlags)->emoji($this->flag_code) : null;
    }

    /**
     * @return BelongsTo<Region, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Places in their hand-set order (positions count within each place type), then as recorded.
     *
     * @return HasMany<Place, $this>
     */
    public function places(): HasMany
    {
        return $this->hasMany(Place::class)->orderBy('position')->orderBy('id');
    }
}
