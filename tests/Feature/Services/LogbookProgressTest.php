<?php

use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;
use App\Models\Region;
use App\Services\LogbookProgress;
use Illuminate\Database\Eloquent\Collection;

function regionsWithMembers(): Collection
{
    return Region::query()->orderBy('position')->with('members')->get();
}

it('counts only completed countries as done in a region', function () {
    Country::factory()->completed()->create(['name' => 'Latvia']);
    Country::factory()->partial()->create(['name' => 'Lithuania']);
    Country::factory()->notCompleted()->create(['name' => 'Estonia']);
    Region::factory()->withMembers(['Latvia', 'Lithuania', 'Estonia'])->create(['name' => 'Baltics']);

    $progress = (new LogbookProgress)->countryProgress(regionsWithMembers());

    expect($progress['regions'][0])
        ->done->toBe(1)
        ->total->toBe(3);
});

it('counts region countries missing from the logbook toward the total as not completed', function () {
    Country::factory()->completed()->create(['name' => 'Spain']);
    Region::factory()->withMembers(['Spain', 'Portugal', 'Andorra'])->create();

    $progress = (new LogbookProgress)->countryProgress(regionsWithMembers());

    expect($progress['regions'][0])
        ->done->toBe(1)
        ->total->toBe(3);
});

it('matches region countries to the logbook ignoring case and outer spaces', function () {
    Country::factory()->completed()->create(['name' => 'Timor-Leste']);
    Region::factory()->withMembers([' timor-leste '])->create();

    $progress = (new LogbookProgress)->countryProgress(regionsWithMembers());

    expect($progress['regions'][0]['done'])->toBe(1);
});

it('counts a country listed in two regions once in the world total', function () {
    Country::factory()->completed()->create(['name' => 'Mexico']);
    Region::factory()->withMembers(['Mexico', 'Canada'])->create(['position' => 1]);
    Region::factory()->withMembers(['MEXICO', 'Guatemala'])->create(['position' => 2]);

    $progress = (new LogbookProgress)->countryProgress(regionsWithMembers());

    expect($progress['world'])->toBe(['done' => 1, 'total' => 3, 'percent' => 33]);
});

it('reports zero percent for the world when no region lists a country', function () {
    Region::factory()->create();

    $progress = (new LogbookProgress)->countryProgress(regionsWithMembers());

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
