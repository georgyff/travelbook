<?php

use App\Enums\CompletionStatus;
use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;

describe('create and store', function () {
    it('names the capitals the country already has', function () {
        $country = Country::factory()->create(['name' => 'Netherlands']);
        Place::factory()->for($country)->capital()->create(['name' => 'Amsterdam']);
        Place::factory()->for($country)->capital()->create(['name' => 'The Hague']);

        $this->get(route('countries.places.create', $country))
            ->assertSee('Netherlands already has capitals: Amsterdam and The Hague. A country can have more than one.');
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

    it('adds another capital without unmarking the existing one, at the end of the capitals', function () {
        $country = Country::factory()->create();
        $type = PlaceType::factory()->create();
        $firstCapital = Place::factory()->for($country)->for($type)->capital()->create(['position' => 1]);
        Place::factory()->for($country)->for($type)->create(['position' => 7]);

        $this->postJson(route('countries.places.store', $country), [
            'name' => 'Second capital',
            'place_type_id' => $type->id,
            'is_capital' => '1',
            'status' => 'partial',
        ])->assertOk();

        expect($firstCapital->fresh()->is_capital)->toBeTrue();
        expect(Place::query()->where('name', 'Second capital')->sole())
            ->is_capital->toBeTrue()
            ->position->toBe(2);
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

    it('adds a place at the end of its type in that country', function () {
        $country = Country::factory()->create();
        $city = PlaceType::factory()->create();
        Place::factory()->for($country)->for($city)->create(['position' => 4]);
        Place::factory()->for($country)->create(['position' => 9]);
        Place::factory()->for($city)->create(['position' => 9]);

        $this->postJson(route('countries.places.store', $country), [
            'name' => 'Kampot',
            'place_type_id' => $city->id,
            'status' => 'partial',
        ])->assertOk();

        expect(Place::query()->where('name', 'Kampot')->sole()->position)->toBe(5);
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

    it('moves a place given another type to the end of that type, and keeps it in place otherwise', function () {
        $country = Country::factory()->create();
        $city = PlaceType::factory()->create();
        $village = PlaceType::factory()->create();
        Place::factory()->for($country)->for($village)->create(['position' => 3]);
        $place = Place::factory()->for($country)->for($city)->create(['position' => 2]);

        $this->putJson(route('places.update', $place), [
            'name' => 'Renamed',
            'place_type_id' => $city->id,
            'status' => 'partial',
        ])->assertOk();
        expect($place->fresh()->position)->toBe(2);

        $this->putJson(route('places.update', $place), [
            'name' => 'Renamed',
            'place_type_id' => $village->id,
            'status' => 'partial',
        ])->assertOk();
        expect($place->fresh()->position)->toBe(4);
    });

    it('clears the capital flag when the box is unticked, moving the place to the end of its type', function () {
        $country = Country::factory()->create();
        $city = PlaceType::factory()->create();
        Place::factory()->for($country)->for($city)->create(['position' => 3]);
        $place = Place::factory()->for($country)->for($city)->capital()->create(['position' => 1]);

        $this->putJson(route('places.update', $place), [
            'name' => $place->name,
            'place_type_id' => $city->id,
            'status' => 'completed',
        ])->assertOk();

        expect($place->fresh())
            ->is_capital->toBeFalse()
            ->position->toBe(4);
    });

    it('deletes a place and returns to its country', function () {
        $place = Place::factory()->create();

        $response = $this->deleteJson(route('places.destroy', $place));

        $response->assertOk()->assertJson(['redirect' => route('countries.show', $place->country_id)]);
        $this->assertModelMissing($place);
    });
});
