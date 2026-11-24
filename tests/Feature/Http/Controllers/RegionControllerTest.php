<?php

use App\Models\Country;
use App\Models\Region;

describe('create and store', function () {
    it('suggests logbook countries in the form', function () {
        Country::factory()->create(['name' => 'Cambodia']);

        $this->get(route('regions.create'))->assertSee('<option value="Cambodia"></option>', false);
    });

    it('adds a region with its countries in order, at the end of the list', function () {
        Region::factory()->create(['position' => 2]);

        $response = $this->postJson(route('regions.store'), [
            'name' => 'Baltics',
            'countries' => [' Latvia ', '', 'Lithuania', 'Estonia'],
        ]);

        $response->assertOk();
        $region = Region::query()->where('name', 'Baltics')->sole();
        expect($region->position)->toBe(3);
        expect($region->members()->pluck('name')->all())->toBe(['Latvia', 'Lithuania', 'Estonia']);
    });

    it('rejects the same country twice in one region, ignoring case', function () {
        $response = $this->postJson(route('regions.store'), [
            'name' => 'Baltics',
            'countries' => ['Latvia', 'latvia'],
        ]);

        $response->assertJsonValidationErrors(['countries.1' => 'This country is already in the list.']);
        $this->assertDatabaseCount('regions', 0);
    });

    it('rejects a duplicate region name', function () {
        Region::factory()->create(['name' => 'Iberia']);

        $response = $this->postJson(route('regions.store'), ['name' => 'Iberia']);

        $response->assertJsonValidationErrors(['name' => 'There is already a region with this name.']);
    });
});

describe('update and destroy', function () {
    it('replaces the country list', function () {
        $region = Region::factory()->withMembers(['Spain', 'Portugal'])->create(['name' => 'Iberia']);

        $this->putJson(route('regions.update', $region), [
            'name' => 'Iberian Peninsula',
            'countries' => ['Portugal', 'Spain', 'Andorra'],
        ])->assertOk();

        expect($region->fresh()->name)->toBe('Iberian Peninsula');
        expect($region->members()->pluck('name')->all())->toBe(['Portugal', 'Spain', 'Andorra']);
    });

    it('deletes a region and its country list but not the countries', function () {
        $country = Country::factory()->create(['name' => 'Spain']);
        $region = Region::factory()->withMembers(['Spain'])->create();

        $this->deleteJson(route('regions.destroy', $region))->assertOk();

        $this->assertModelMissing($region);
        $this->assertDatabaseCount('region_countries', 0);
        $this->assertModelExists($country);
    });
});
