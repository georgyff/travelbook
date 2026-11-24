<?php

use App\Enums\CompletionStatus;
use App\Models\Continent;
use App\Models\Country;
use App\Models\Place;
use App\Models\Region;

describe('create and store', function () {
    it('preselects the continent the form was opened from', function () {
        $continent = Continent::factory()->create();

        $response = $this->get(route('countries.create', ['continent' => $continent->id]));

        $response->assertSee('<option value="'.$continent->id.'" selected>', false);
    });

    it('adds a country at the end of its continent and opens it', function () {
        $continent = Continent::factory()->create();
        Country::factory()->for($continent)->create(['position' => 3]);

        $response = $this->postJson(route('countries.store'), [
            'name' => ' Portugal ',
            'continent_id' => $continent->id,
            'status' => 'completed',
            'history' => "Line one.\nLine two.",
        ]);

        $country = Country::query()->where('name', 'Portugal')->sole();
        $response->assertOk()->assertJson(['redirect' => route('countries.show', $country)]);
        expect($country)
            ->position->toBe(4)
            ->status->toBe(CompletionStatus::Completed)
            ->history->toBe("Line one.\nLine two.");
    });

    it('requires a name, continent and status', function () {
        $response = $this->postJson(route('countries.store'), []);

        $response->assertJsonValidationErrors([
            'name' => 'Give the country a name.',
            'continent_id' => 'Choose a continent.',
            'status' => 'Choose a status.',
        ]);
    });

    it('rejects an unknown status', function () {
        $response = $this->postJson(route('countries.store'), [
            'name' => 'Peru',
            'continent_id' => Continent::factory()->create()->id,
            'status' => 'visited',
        ]);

        $response->assertJsonValidationErrors(['status']);
        $this->assertDatabaseMissing('countries', ['name' => 'Peru']);
    });

    it('rejects a name that differs from an existing country only by case', function () {
        $existing = Country::factory()->create(['name' => 'Cambodia']);

        $response = $this->postJson(route('countries.store'), [
            'name' => 'CAMBODIA',
            'continent_id' => $existing->continent_id,
            'status' => 'partial',
        ]);

        $response->assertJsonValidationErrors(['name' => 'There is already a country with this name.']);
    });
});

describe('update', function () {
    it('moves a country to the end of its new continent', function () {
        $asia = Continent::factory()->create();
        $europe = Continent::factory()->create();
        Country::factory()->for($europe)->create(['position' => 2]);
        $country = Country::factory()->for($asia)->create(['name' => 'Georgia', 'position' => 1]);

        $this->putJson(route('countries.update', $country), [
            'name' => 'Georgia',
            'continent_id' => $europe->id,
            'status' => 'not_completed',
        ])->assertOk();

        expect($country->fresh())
            ->continent_id->toBe($europe->id)
            ->position->toBe(3);
    });

    it('renames the country in every region that listed it', function () {
        $country = Country::factory()->create(['name' => 'Burma']);
        $region = Region::factory()->withMembers(['Thailand', 'burma'])->create();

        $this->putJson(route('countries.update', $country), [
            'name' => 'Myanmar',
            'continent_id' => $country->continent_id,
            'status' => 'partial',
        ])->assertOk();

        expect($region->members()->pluck('name')->all())->toBe(['Thailand', 'Myanmar']);
    });

    it('drops the old name from a region that already lists the new one', function () {
        $country = Country::factory()->create(['name' => 'Burma']);
        $region = Region::factory()->withMembers(['Burma', 'Myanmar'])->create();

        $this->putJson(route('countries.update', $country), [
            'name' => 'Myanmar',
            'continent_id' => $country->continent_id,
            'status' => 'partial',
        ])->assertOk();

        expect($region->members()->pluck('name')->all())->toBe(['Myanmar']);
    });
});

describe('destroy', function () {
    it('deletes the country and its places but keeps region names', function () {
        $country = Country::factory()->create(['name' => 'Laos']);
        $place = Place::factory()->for($country)->create();
        $region = Region::factory()->withMembers(['Laos'])->create();

        $response = $this->deleteJson(route('countries.destroy', $country));

        $response->assertOk()->assertJson(['redirect' => route('logbook')]);
        $this->assertModelMissing($country);
        $this->assertModelMissing($place);
        expect($region->members()->pluck('name')->all())->toBe(['Laos']);
    });
});
