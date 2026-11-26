<?php

namespace App\Models;

use App\Enums\CompletionStatus;
use Database\Factories\PlaceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['country_id', 'place_type_id', 'name', 'is_capital', 'position', 'status', 'short_history', 'facts'])]
class Place extends Model
{
    /** @use HasFactory<PlaceFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'not_completed',
        'is_capital' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CompletionStatus::class,
            'is_capital' => 'boolean',
            'facts' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return BelongsTo<PlaceType, $this>
     */
    public function placeType(): BelongsTo
    {
        return $this->belongsTo(PlaceType::class);
    }
}
