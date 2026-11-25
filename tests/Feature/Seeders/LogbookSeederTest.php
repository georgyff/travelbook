<?php

use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;
use App\Models\Region;
use Database\Seeders\LogbookSeeder;

it('seeds the handoff sample data', function () {
    $this->seed(LogbookSeeder::class);

    expect(PlaceType::query()->orderBy('position')->pluck('plural_name')->all())->toBe(['Cities', 'Villages', 'Nature', 'Monuments']);
    expect(Region::query()->orderBy('position')->pluck('name')->all())->toBe(['Asia', 'Europe', 'Africa', 'North America']);
    expect(Region::query()->where('name', 'Asia')->sole()->countries()->pluck('name')->all())->toBe(['Cambodia', 'Thailand', 'Vietnam', 'Japan']);
    expect(Country::query()->count())->toBe(11);
    expect(Place::query()->count())->toBe(32);
    expect(Place::query()->where('name', 'Angkor Wat')->sole()->facts)->toHaveCount(3);
});

it('reuses place types that already exist', function () {
    PlaceType::factory()->create(['name' => 'City', 'plural_name' => 'Towns']);

    $this->seed(LogbookSeeder::class);

    expect(PlaceType::query()->where('name', 'City')->count())->toBe(1);
    expect(PlaceType::query()->count())->toBe(4);
});

it('leaves an existing logbook untouched', function () {
    Region::factory()->create(['name' => 'Mine']);

    $this->seed(LogbookSeeder::class);

    expect(Region::query()->pluck('name')->all())->toBe(['Mine']);
    expect(Country::query()->count())->toBe(0);
});
