<?php

use App\Models\Country;
use App\Models\Region;

it('saves the new country order within a region', function () {
    $region = Region::factory()->create();
    $first = Country::factory()->for($region)->create(['position' => 1]);
    $second = Country::factory()->for($region)->create(['position' => 2]);

    $response = $this->putJson(route('regions.country-order.update', $region), ['ids' => [$second->id, $first->id]]);

    $response->assertNoContent();
    expect($region->countries()->pluck('id')->all())->toBe([$second->id, $first->id]);
});

it('rejects countries from another region', function () {
    $region = Region::factory()->create();
    $own = Country::factory()->for($region)->create(['position' => 1]);
    $foreign = Country::factory()->create(['position' => 5]);

    $response = $this->putJson(route('regions.country-order.update', $region), ['ids' => [$foreign->id, $own->id]]);

    $response->assertJsonValidationErrors(['ids']);
    expect($foreign->fresh()->position)->toBe(5);
});
