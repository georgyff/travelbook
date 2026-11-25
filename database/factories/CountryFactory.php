<?php

namespace Database\Factories;

use App\Enums\CompletionStatus;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'region_id' => Region::factory(),
            'name' => fake()->unique()->country(),
            'status' => CompletionStatus::NotCompleted,
            'history' => null,
            'position' => 0,
        ];
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
