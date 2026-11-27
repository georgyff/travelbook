<?php

namespace Database\Factories;

use App\Enums\TripStatus;
use App\Enums\TripType;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'status' => TripStatus::Idea,
            'type' => null,
            'starts_on' => null,
            'ends_on' => null,
            'days' => null,
            'walked_steps' => null,
            'walked_km' => null,
            'pictures' => null,
            'cost_travel' => null,
            'cost_housing' => null,
            'cost_in_travel' => null,
        ];
    }

    /**
     * A finished trip with every figure filled in.
     */
    public function done(): static
    {
        return $this->state(fn (): array => [
            'status' => TripStatus::Done,
            'type' => TripType::Friends,
            'starts_on' => '2026-05-03',
            'ends_on' => '2026-05-12',
            'days' => 10,
            'walked_steps' => 182000,
            'walked_km' => 131.5,
            'pictures' => 412,
            'cost_travel' => 320,
            'cost_housing' => 540,
            'cost_in_travel' => 450,
        ]);
    }
}
