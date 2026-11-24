<?php

use App\Models\Continent;
use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;
use App\Models\Region;
use Database\Seeders\LogbookSeeder;

it('seeds the handoff sample data', function () {
    $this->seed(LogbookSeeder::class);

    expect(PlaceType::query()->orderBy('position')->pluck('plural_name')->all())->toBe(['Cities', 'Villages', 'Nature', 'Monuments']);
    expect(Continent::query()->count())->toBe(4);
    expect(Country::query()->count())->toBe(11);
    expect(Place::query()->count())->toBe(32);
    expect(Region::query()->where('name', 'Southeast Asia')->sole()->members()->count())->toBe(11);
    expect(Place::query()->where('name', 'Angkor Wat')->sole()->facts)->toHaveCount(3);
});

it('leaves an existing logbook untouched', function () {
    Continent::factory()->create(['name' => 'Mine']);

    $this->seed(LogbookSeeder::class);

    expect(Continent::query()->pluck('name')->all())->toBe(['Mine']);
    expect(Country::query()->count())->toBe(0);
});
