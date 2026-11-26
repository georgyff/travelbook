<?php

use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;

it('saves the new order of a country’s places of one type', function () {
    $country = Country::factory()->create();
    $city = PlaceType::factory()->create();
    $first = Place::factory()->for($country)->for($city)->create(['position' => 1]);
    $second = Place::factory()->for($country)->for($city)->create(['position' => 2]);

    $response = $this->putJson(route('countries.place-order.update', [$country, $city]), ['ids' => [$second->id, $first->id]]);

    $response->assertNoContent();
    expect($country->places()->pluck('id')->all())->toBe([$second->id, $first->id]);
});

it('rejects places of another type, another country, or the capital', function (string $intruder) {
    $country = Country::factory()->create();
    $city = PlaceType::factory()->create();
    $own = Place::factory()->for($country)->for($city)->create(['position' => 1]);

    $other = match ($intruder) {
        'type' => Place::factory()->for($country)->create(['position' => 5]),
        'country' => Place::factory()->for($city)->create(['position' => 5]),
        'capital' => Place::factory()->for($country)->for($city)->capital()->create(['position' => 5]),
    };

    $response = $this->putJson(route('countries.place-order.update', [$country, $city]), ['ids' => [$other->id, $own->id]]);

    $response->assertJsonValidationErrors(['ids']);
    expect($other->fresh()->position)->toBe(5);
})->with(['type', 'country', 'capital']);

it('saves the new order of a country’s capitals', function () {
    $country = Country::factory()->create();
    $amsterdam = Place::factory()->for($country)->capital()->create(['position' => 1]);
    $theHague = Place::factory()->for($country)->capital()->create(['position' => 2]);
    $city = Place::factory()->for($country)->create(['position' => 1]);

    $this->putJson(route('countries.capital-order.update', $country), ['ids' => [$theHague->id, $amsterdam->id]])
        ->assertNoContent();

    expect([$theHague->fresh()->position, $amsterdam->fresh()->position])->toBe([1, 2]);
    $this->putJson(route('countries.capital-order.update', $country), ['ids' => [$city->id, $amsterdam->id, $theHague->id]])
        ->assertJsonValidationErrors(['ids']);
});

it('shows the places in their saved order, each group reorderable', function () {
    $country = Country::factory()->create();
    $city = PlaceType::factory()->create(['name' => 'City', 'plural_name' => 'Cities']);
    Place::factory()->for($country)->for($city)->create(['name' => 'Kampot', 'position' => 2]);
    Place::factory()->for($country)->for($city)->create(['name' => 'Battambang', 'position' => 1]);
    Place::factory()->for($country)->for($city)->capital()->create(['name' => 'Phnom Penh', 'position' => 3]);

    $response = $this->get(route('countries.show', $country));

    $response->assertSeeInOrder(['Capital</h3>', 'Phnom Penh', 'Cities</h3>', 'Battambang', 'Kampot'], false);
    $response->assertSee('data-sort-url="'.route('countries.capital-order.update', $country).'"', false);
    $response->assertSee('data-sort-url="'.route('countries.place-order.update', [$country, $city]).'"', false);
    $response->assertSee('aria-label="Reorder Kampot"', false);
    $response->assertSee('aria-label="Reorder Phnom Penh"', false);
});
