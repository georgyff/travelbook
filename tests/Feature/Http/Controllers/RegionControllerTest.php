<?php

use App\Models\Country;
use App\Models\Region;

describe('create and store', function () {
    it('renders the add form', function () {
        $this->get(route('regions.create'))->assertSee('Add a region');
    });

    it('adds a region at the end of the order', function () {
        Region::factory()->create(['position' => 4]);

        $response = $this->postJson(route('regions.store'), ['name' => '  Oceania ']);

        $response->assertOk()->assertJson(['redirect' => route('logbook')]);
        $this->assertDatabaseHas('regions', ['name' => 'Oceania', 'position' => 5]);
    });

    it('rejects a missing name', function () {
        $response = $this->postJson(route('regions.store'), ['name' => '']);

        $response->assertJsonValidationErrors(['name' => 'Give the region a name.']);
        $this->assertDatabaseCount('regions', 0);
    });

    it('rejects a duplicate name', function () {
        Region::factory()->create(['name' => 'Asia']);

        $response = $this->postJson(route('regions.store'), ['name' => 'Asia']);

        $response->assertJsonValidationErrors(['name' => 'There is already a region with this name.']);
    });
});

describe('edit and update', function () {
    it('offers delete only for an empty region', function () {
        $empty = Region::factory()->create(['name' => 'Antarctica']);
        $full = Region::factory()->create(['name' => 'Asia']);
        Country::factory()->for($full)->count(2)->create();

        $this->get(route('regions.edit', $empty))->assertSee('data-delete-action', false);
        $this->get(route('regions.edit', $full))
            ->assertDontSee('data-delete-action', false)
            ->assertSee('To delete Asia, first move or delete its 2 countries.');
    });

    it('renames a region and keeps its own name valid', function () {
        $region = Region::factory()->create(['name' => 'Asia']);

        $this->putJson(route('regions.update', $region), ['name' => 'Asia'])->assertOk();
        $this->putJson(route('regions.update', $region), ['name' => 'Southeast Asia'])->assertOk();

        expect($region->fresh()->name)->toBe('Southeast Asia');
    });
});

describe('destroy', function () {
    it('deletes an empty region', function () {
        $region = Region::factory()->create();

        $this->deleteJson(route('regions.destroy', $region))->assertOk();

        $this->assertModelMissing($region);
    });

    it('refuses to delete a region that still has countries', function () {
        $region = Region::factory()->create(['name' => 'Asia']);
        $country = Country::factory()->for($region)->create();

        $response = $this->deleteJson(route('regions.destroy', $region));

        $response->assertJsonValidationErrors(['region' => 'Move or delete the 1 country in Asia first.']);
        $this->assertModelExists($region);
        $this->assertModelExists($country);
    });
});
