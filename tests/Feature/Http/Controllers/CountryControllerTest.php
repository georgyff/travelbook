<?php

use App\Enums\CompletionStatus;
use App\Models\Country;
use App\Models\Place;
use App\Models\Region;

describe('create and store', function () {
    it('preselects the region the form was opened from', function () {
        $region = Region::factory()->create();

        $response = $this->get(route('countries.create', ['region' => $region->id]));

        $response->assertSee('<option value="'.$region->id.'" selected>', false);
    });

    it('adds a country at the end of its region and opens it', function () {
        $region = Region::factory()->create();
        Country::factory()->for($region)->create(['position' => 3]);

        $response = $this->postJson(route('countries.store'), [
            'name' => ' Portugal ',
            'region_id' => $region->id,
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

    it('requires a name, region and status', function () {
        $response = $this->postJson(route('countries.store'), []);

        $response->assertJsonValidationErrors([
            'name' => 'Give the country a name.',
            'region_id' => 'Choose a region.',
            'status' => 'Choose a status.',
        ]);
    });

    it('rejects an unknown status', function () {
        $response = $this->postJson(route('countries.store'), [
            'name' => 'Peru',
            'region_id' => Region::factory()->create()->id,
            'status' => 'visited',
        ]);

        $response->assertJsonValidationErrors(['status']);
        $this->assertDatabaseMissing('countries', ['name' => 'Peru']);
    });

    it('rejects a name that differs from an existing country only by case', function () {
        $existing = Country::factory()->create(['name' => 'Cambodia']);

        $response = $this->postJson(route('countries.store'), [
            'name' => 'CAMBODIA',
            'region_id' => $existing->region_id,
            'status' => 'partial',
        ]);

        $response->assertJsonValidationErrors(['name' => 'There is already a country with this name.']);
    });
});

describe('update', function () {
    it('moves a country to the end of its new region', function () {
        $asia = Region::factory()->create();
        $europe = Region::factory()->create();
        Country::factory()->for($europe)->create(['position' => 2]);
        $country = Country::factory()->for($asia)->create(['name' => 'Georgia', 'position' => 1]);

        $this->putJson(route('countries.update', $country), [
            'name' => 'Georgia',
            'region_id' => $europe->id,
            'status' => 'not_completed',
        ])->assertOk();

        expect($country->fresh())
            ->region_id->toBe($europe->id)
            ->position->toBe(3);
    });

});

describe('destroy', function () {
    it('deletes the country and its places but keeps its region', function () {
        $country = Country::factory()->create();
        $place = Place::factory()->for($country)->create();

        $response = $this->deleteJson(route('countries.destroy', $country));

        $response->assertOk()->assertJson(['redirect' => route('logbook')]);
        $this->assertModelMissing($country);
        $this->assertModelMissing($place);
        $this->assertModelExists($country->region);
    });
});
