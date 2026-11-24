<?php

use App\Enums\CompletionStatus;
use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;

describe('create and store', function () {
    it('warns that ticking capital replaces the current capital', function () {
        $country = Country::factory()->create();
        Place::factory()->for($country)->capital()->create(['name' => 'Phnom Penh']);

        $this->get(route('countries.places.create', $country))
            ->assertSee('Ticking this replaces Phnom Penh.');
    });

    it('adds a place with trimmed facts and opens it', function () {
        $country = Country::factory()->create();
        $type = PlaceType::factory()->create();

        $response = $this->postJson(route('countries.places.store', $country), [
            'name' => 'Angkor Wat',
            'place_type_id' => $type->id,
            'status' => 'completed',
            'short_history' => 'Built in the 12th century.',
            'facts' => [' First fact ', '', '   ', 'Second fact'],
        ]);

        $place = Place::query()->where('name', 'Angkor Wat')->sole();
        $response->assertOk()->assertJson(['redirect' => route('countries.show', [$country, 'place' => $place->id])]);
        expect($place)
            ->country_id->toBe($country->id)
            ->is_capital->toBeFalse()
            ->status->toBe(CompletionStatus::Completed)
            ->facts->toBe(['First fact', 'Second fact']);
    });

    it('makes the new capital the only capital of its country', function () {
        $country = Country::factory()->create();
        $type = PlaceType::factory()->create();
        $oldCapital = Place::factory()->for($country)->for($type)->capital()->create();
        $otherCountryCapital = Place::factory()->for($type)->capital()->create();

        $this->postJson(route('countries.places.store', $country), [
            'name' => 'New capital',
            'place_type_id' => $type->id,
            'is_capital' => '1',
            'status' => 'partial',
        ])->assertOk();

        expect($oldCapital->fresh()->is_capital)->toBeFalse();
        expect($otherCountryCapital->fresh()->is_capital)->toBeTrue();
        expect(Place::query()->where('name', 'New capital')->sole()->is_capital)->toBeTrue();
    });

    it('requires a name, type and status', function () {
        $country = Country::factory()->create();

        $response = $this->postJson(route('countries.places.store', $country), []);

        $response->assertJsonValidationErrors([
            'name' => 'Give the place a name.',
            'place_type_id' => 'Choose a type.',
            'status' => 'Choose a status.',
        ]);
        $this->assertDatabaseCount('places', 0);
    });

    it('rejects a fact that is too long', function () {
        $country = Country::factory()->create();

        $response = $this->postJson(route('countries.places.store', $country), [
            'name' => 'Kep',
            'place_type_id' => PlaceType::factory()->create()->id,
            'status' => 'partial',
            'facts' => ['Fine', str_repeat('a', 1001)],
        ]);

        $response->assertJsonValidationErrors(['facts.1' => 'Keep each fact under 1000 characters.']);
    });
});

describe('update and destroy', function () {
    it('updates a place and replaces its facts', function () {
        $place = Place::factory()->create(['facts' => ['Old']]);
        $type = PlaceType::factory()->create();

        $response = $this->putJson(route('places.update', $place), [
            'name' => 'Renamed',
            'place_type_id' => $type->id,
            'status' => 'not_completed',
            'facts' => ['New one', 'New two'],
        ]);

        $response->assertOk()->assertJson(['redirect' => route('countries.show', [$place->country_id, 'place' => $place->id])]);
        expect($place->fresh())
            ->name->toBe('Renamed')
            ->place_type_id->toBe($type->id)
            ->facts->toBe(['New one', 'New two']);
    });

    it('clears the capital flag when the box is unticked', function () {
        $place = Place::factory()->capital()->create();

        $this->putJson(route('places.update', $place), [
            'name' => $place->name,
            'place_type_id' => $place->place_type_id,
            'status' => 'completed',
        ])->assertOk();

        expect($place->fresh()->is_capital)->toBeFalse();
    });

    it('deletes a place and returns to its country', function () {
        $place = Place::factory()->create();

        $response = $this->deleteJson(route('places.destroy', $place));

        $response->assertOk()->assertJson(['redirect' => route('countries.show', $place->country_id)]);
        $this->assertModelMissing($place);
    });
});
