<?php

use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;
use App\Models\Region;
use App\Services\LogbookProgress;
use Illuminate\Database\Eloquent\Collection;

function regionsWithCountries(): Collection
{
    return Region::query()->orderBy('position')->with('countries')->get();
}

it('counts only completed countries as done in a region', function () {
    $baltics = Region::factory()->create();
    Country::factory()->for($baltics)->completed()->create();
    Country::factory()->for($baltics)->partial()->create();
    Country::factory()->for($baltics)->notCompleted()->create();

    $progress = (new LogbookProgress)->countryProgress(regionsWithCountries());

    expect($progress['regions'][0])
        ->done->toBe(1)
        ->total->toBe(3);
});

it('reports an empty region as zero of zero', function () {
    Region::factory()->create();

    $progress = (new LogbookProgress)->countryProgress(regionsWithCountries());

    expect($progress['regions'][0])
        ->done->toBe(0)
        ->total->toBe(0);
});

it('sums every region into the world total', function () {
    $asia = Region::factory()->create(['position' => 1]);
    $europe = Region::factory()->create(['position' => 2]);
    Country::factory()->for($asia)->completed()->create();
    Country::factory()->for($asia)->partial()->create();
    Country::factory()->for($europe)->completed()->create();

    $progress = (new LogbookProgress)->countryProgress(regionsWithCountries());

    expect($progress['world'])->toBe(['done' => 2, 'total' => 3, 'percent' => 67]);
});

it('reports zero percent for the world when there are no countries', function () {
    Region::factory()->create();

    $progress = (new LogbookProgress)->countryProgress(regionsWithCountries());

    expect($progress['world'])->toBe(['done' => 0, 'total' => 0, 'percent' => 0]);
});

it('sums completed and partial places per type, counting capitals as their own type', function () {
    $city = PlaceType::factory()->create(['name' => 'City', 'plural_name' => 'Cities', 'position' => 1]);
    $nature = PlaceType::factory()->create(['name' => 'Nature', 'plural_name' => 'Nature', 'position' => 2]);
    Place::factory()->for($city)->capital()->completed()->create();
    Place::factory()->for($city)->completed()->create();
    Place::factory()->for($city)->partial()->create();
    Place::factory()->for($city)->notCompleted()->create();

    $tallies = (new LogbookProgress)->placesByType(PlaceType::query()->orderBy('position')->get());

    expect($tallies)->toHaveCount(2);
    expect($tallies[0])->toMatchArray(['completed' => 2, 'partial' => 1, 'sum' => 3]);
    expect($tallies[0]['placeType']->is($city))->toBeTrue();
    expect($tallies[1])->toMatchArray(['completed' => 0, 'partial' => 0, 'sum' => 0]);
    expect($tallies[1]['placeType']->is($nature))->toBeTrue();
});
