<?php

namespace Database\Factories;

use App\Models\Trip;
use App\Models\TripStop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TripStop>
 */
class TripStopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'position' => 1,
            'visited_on' => null,
            'city' => fake()->city(),
            'transport_cost' => null,
            'hotel_cost' => null,
            'times' => null,
            'country' => null,
        ];
    }
}
