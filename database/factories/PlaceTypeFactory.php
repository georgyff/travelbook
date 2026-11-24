<?php

namespace Database\Factories;

use App\Models\PlaceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlaceType>
 */
class PlaceTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $name = fake()->unique()->word(),
            'plural_name' => str($name)->plural()->ucfirst()->toString(),
            'position' => 0,
        ];
    }
}
