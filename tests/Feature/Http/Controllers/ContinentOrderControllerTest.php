<?php

use App\Models\Continent;

it('saves the new continent order', function () {
    $asia = Continent::factory()->create(['position' => 1]);
    $europe = Continent::factory()->create(['position' => 2]);
    $africa = Continent::factory()->create(['position' => 3]);

    $response = $this->putJson(route('continent-order.update'), ['ids' => [$africa->id, $asia->id, $europe->id]]);

    $response->assertNoContent();
    expect(Continent::query()->orderBy('position')->pluck('id')->all())->toBe([$africa->id, $asia->id, $europe->id]);
});

it('rejects an order that leaves out a continent', function () {
    $asia = Continent::factory()->create(['position' => 1]);
    Continent::factory()->create(['position' => 2]);

    $response = $this->putJson(route('continent-order.update'), ['ids' => [$asia->id]]);

    $response->assertJsonValidationErrors(['ids' => 'The list changed since the page loaded. Reload and try again.']);
});
