<?php

namespace Database\Factories;

use App\Enums\CompletionStatus;
use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Place>
 */
class PlaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'place_type_id' => PlaceType::factory(),
            'name' => fake()->unique()->city(),
            'is_capital' => false,
            'status' => CompletionStatus::NotCompleted,
            'times_visited' => null,
            'pictures' => null,
            'short_history' => null,
            'facts' => [],
        ];
    }

    public function capital(): static
    {
        return $this->state(['is_capital' => true]);
    }

    public function completed(): static
    {
        return $this->state(['status' => CompletionStatus::Completed]);
    }

    public function partial(): static
    {
        return $this->state(['status' => CompletionStatus::Partial]);
    }

    public function notCompleted(): static
    {
        return $this->state(['status' => CompletionStatus::NotCompleted]);
    }
}
