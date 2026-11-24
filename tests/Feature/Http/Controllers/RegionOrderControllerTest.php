<?php

use App\Models\Region;

it('saves the new region order', function () {
    $baltics = Region::factory()->create(['position' => 1]);
    $iberia = Region::factory()->create(['position' => 2]);

    $response = $this->putJson(route('region-order.update'), ['ids' => [$iberia->id, $baltics->id]]);

    $response->assertNoContent();
    expect(Region::query()->orderBy('position')->pluck('id')->all())->toBe([$iberia->id, $baltics->id]);
});

it('rejects an id listed twice', function () {
    $baltics = Region::factory()->create(['position' => 1]);
    Region::factory()->create(['position' => 2]);

    $response = $this->putJson(route('region-order.update'), ['ids' => [$baltics->id, $baltics->id]]);

    $response->assertJsonValidationErrors(['ids.0']);
});
