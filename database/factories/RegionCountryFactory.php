<?php

namespace Database\Factories;

use App\Models\Region;
use App\Models\RegionCountry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegionCountry>
 */
class RegionCountryFactory extends Factory
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
            'position' => 0,
        ];
    }
}
