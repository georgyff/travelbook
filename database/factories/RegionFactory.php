<?php

namespace Database\Factories;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Region>
 */
class RegionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'position' => 0,
        ];
    }

    /**
     * Give the region these member country names, in order.
     *
     * @param  list<string>  $names
     */
    public function withMembers(array $names): static
    {
        return $this->afterCreating(function (Region $region) use ($names): void {
            foreach (array_values($names) as $position => $name) {
                $region->members()->create(['name' => $name, 'position' => $position]);
            }
        });
    }
}
