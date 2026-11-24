<?php

use App\Models\Continent;
use App\Models\Country;

it('saves the new country order within a continent', function () {
    $continent = Continent::factory()->create();
    $first = Country::factory()->for($continent)->create(['position' => 1]);
    $second = Country::factory()->for($continent)->create(['position' => 2]);

    $response = $this->putJson(route('continents.country-order.update', $continent), ['ids' => [$second->id, $first->id]]);

    $response->assertNoContent();
    expect($continent->countries()->pluck('id')->all())->toBe([$second->id, $first->id]);
});

it('rejects countries from another continent', function () {
    $continent = Continent::factory()->create();
    $own = Country::factory()->for($continent)->create(['position' => 1]);
    $foreign = Country::factory()->create(['position' => 5]);

    $response = $this->putJson(route('continents.country-order.update', $continent), ['ids' => [$foreign->id, $own->id]]);

    $response->assertJsonValidationErrors(['ids']);
    expect($foreign->fresh()->position)->toBe(5);
});
