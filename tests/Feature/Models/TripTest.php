<?php

use App\Models\Country;
use App\Models\Region;
use App\Models\Trip;

it('sums the three cost categories, or has no total when none is filled in', function () {
    expect(Trip::factory()->make(['cost_travel' => 320, 'cost_housing' => null, 'cost_in_travel' => 450.5])->totalCost())->toBe(770.5);
    expect(Trip::factory()->make()->totalCost())->toBeNull();
});

it('works out the average per day as in-travel divided by days', function () {
    expect(Trip::factory()->make(['cost_in_travel' => 450, 'days' => 10, 'cost_housing' => 999])->averagePerDay())->toBe(45.0);
    expect(Trip::factory()->make(['cost_in_travel' => 450, 'days' => 0])->averagePerDay())->toBeNull();
    expect(Trip::factory()->make(['cost_in_travel' => null, 'days' => 10])->averagePerDay())->toBeNull();
});

it('writes the dates as a compact range', function (?string $start, ?string $end, ?string $range) {
    expect(Trip::factory()->make(['starts_on' => $start, 'ends_on' => $end])->dateRange())->toBe($range);
})->with([
    'same month' => ['2026-05-03', '2026-05-17', '3 → 17 May 2026'],
    'same year' => ['2026-04-28', '2026-05-05', '28 Apr → 5 May 2026'],
    'across years' => ['2025-12-28', '2026-01-04', '28 Dec 2025 → 4 Jan 2026'],
    'one day' => ['2026-05-03', '2026-05-03', '3 May 2026'],
    'start only' => ['2026-05-03', null, 'from 3 May 2026'],
    'no dates' => [null, null, null],
]);

it('lists the regions of its countries once each, in Logbook order', function () {
    $asia = Region::factory()->create(['name' => 'Asia', 'position' => 2]);
    $europe = Region::factory()->create(['name' => 'Europe', 'position' => 1]);
    $trip = Trip::factory()->create();
    $trip->countries()->attach([
        Country::factory()->for($asia)->create()->id => ['position' => 1],
        Country::factory()->for($europe)->create()->id => ['position' => 2],
        Country::factory()->for($asia)->create()->id => ['position' => 3],
    ]);

    expect($trip->load('countries.region')->regions()->pluck('name')->all())->toBe(['Europe', 'Asia']);
});
