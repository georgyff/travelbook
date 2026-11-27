<?php

use App\Models\Trip;
use App\Models\TripStop;

it('saves the itinerary in the order typed, dropping empty rows', function () {
    $trip = Trip::factory()->create();
    TripStop::factory()->for($trip)->create(['city' => 'Old row']);

    $response = $this->putJson(route('trips.itinerary.update', $trip), [
        'stop_dates' => ['2026-05-03', null, '2026-05-06'],
        'stop_cities' => [' Amsterdam ', '  ', 'Brussels'],
        'stop_transport' => ['45.50', null, '29'],
        'stop_hotel' => ['240', null, null],
        'stop_times' => ['08:15 → 11:40', '', '14:02 → 16:55'],
        'stop_countries' => ['Netherlands', null, 'Belgium'],
    ]);

    $response->assertOk()->assertJson(['redirect' => route('trips.show', $trip)]);
    $stops = $trip->stops()->get();
    expect($stops)->toHaveCount(2);
    expect($stops[0])
        ->position->toBe(1)
        ->visited_on->format('Y-m-d')->toBe('2026-05-03')
        ->city->toBe('Amsterdam')
        ->transport_cost->toBe(45.5)
        ->hotel_cost->toBe(240.0)
        ->times->toBe('08:15 → 11:40')
        ->country->toBe('Netherlands');
    expect($stops[1])
        ->position->toBe(2)
        ->city->toBe('Brussels')
        ->hotel_cost->toBeNull();
});

it('points errors at the cell typed in', function () {
    $trip = Trip::factory()->create();

    $response = $this->putJson(route('trips.itinerary.update', $trip), [
        'stop_dates' => [null, 'not a date'],
        'stop_cities' => ['Amsterdam', 'Brussels'],
        'stop_transport' => ['ten', null],
        'stop_hotel' => [null, '-4'],
        'stop_times' => [null, null],
        'stop_countries' => [null, null],
    ]);

    $response->assertJsonValidationErrors([
        'stop_dates.1' => 'Use a date.',
        'stop_transport.0' => 'The transport cost must be a number.',
        'stop_hotel.1' => 'Use zero or more.',
    ]);
    expect($trip->stops()->count())->toBe(0);
});

it('clears the table when every row is removed', function () {
    $trip = Trip::factory()->create();
    TripStop::factory()->for($trip)->count(2)->create();

    $this->putJson(route('trips.itinerary.update', $trip), [])->assertOk();

    expect($trip->stops()->count())->toBe(0);
});

it('shows the six columns in order, with cost totals', function () {
    $trip = Trip::factory()->create();
    TripStop::factory()->for($trip)->create(['position' => 2, 'visited_on' => '2026-05-06', 'city' => 'Brussels', 'transport_cost' => 29, 'hotel_cost' => null, 'times' => '14:02 → 16:55', 'country' => 'Belgium']);
    TripStop::factory()->for($trip)->create(['position' => 1, 'visited_on' => '2026-05-03', 'city' => 'Amsterdam', 'transport_cost' => 45.5, 'hotel_cost' => 240, 'times' => '08:15 → 11:40', 'country' => 'Netherlands']);

    $response = $this->get(route('trips.show', $trip));

    $response->assertSeeInOrder(['Itinerary', 'Date', 'City', 'Transport cost', 'Hotel cost', 'Dep. &amp; arr. times', 'Country'], false);
    $response->assertSeeInOrder(['3 May 2026', 'Amsterdam', '€45.50', '€240', '08:15 → 11:40', 'Netherlands', '6 May 2026', 'Brussels', '€29'], false);
    $response->assertSeeInOrder(['Total', '€74.50', '€240'], false);
});

it('says when there is no itinerary', function () {
    $this->get(route('trips.show', Trip::factory()->create()))->assertSee('No itinerary yet.');
});

it('opens the table editor with the existing rows and a blank row template', function () {
    $trip = Trip::factory()->create();
    TripStop::factory()->for($trip)->create(['city' => 'Amsterdam', 'times' => '08:15 → 11:40']);

    $response = $this->get(route('trips.itinerary.edit', $trip));

    $response->assertOk();
    $response->assertSee('name="stop_cities[]" value="Amsterdam"', false);
    $response->assertSee('value="08:15 → 11:40"', false);
    $response->assertSee('<template data-list-template>', false);
});

it('deletes the itinerary along with their trip', function () {
    $trip = Trip::factory()->create();
    TripStop::factory()->for($trip)->create();

    $this->deleteJson(route('trips.destroy', $trip))->assertOk();

    $this->assertDatabaseCount('trip_stops', 0);
});
