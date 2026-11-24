<?php

use App\Models\Country;

it('renders the history form with the current text', function () {
    $country = Country::factory()->create(['name' => 'Cambodia', 'history' => 'Old text.']);

    $this->get(route('countries.history.edit', $country))
        ->assertSee('History of Cambodia')
        ->assertSee('Old text.');
});

it('saves the history keeping line breaks', function () {
    $country = Country::factory()->create();

    $this->putJson(route('countries.history.update', $country), ['history' => "First.\n\nSecond."])->assertOk();

    expect($country->fresh()->history)->toBe("First.\n\nSecond.");
});

it('clears the history when the text is emptied', function () {
    $country = Country::factory()->create(['history' => 'Old text.']);

    $this->putJson(route('countries.history.update', $country), ['history' => ''])->assertOk();

    expect($country->fresh()->history)->toBeNull();
});
