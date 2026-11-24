<?php

use App\Models\Continent;
use App\Models\Country;

describe('create and store', function () {
    it('renders the add form', function () {
        $this->get(route('continents.create'))->assertSee('Add a continent');
    });

    it('adds a continent at the end of the order', function () {
        Continent::factory()->create(['position' => 4]);

        $response = $this->postJson(route('continents.store'), ['name' => '  Oceania ']);

        $response->assertOk()->assertJson(['redirect' => route('logbook')]);
        $this->assertDatabaseHas('continents', ['name' => 'Oceania', 'position' => 5]);
    });

    it('rejects a missing name', function () {
        $response = $this->postJson(route('continents.store'), ['name' => '']);

        $response->assertJsonValidationErrors(['name' => 'Give the continent a name.']);
        $this->assertDatabaseCount('continents', 0);
    });

    it('rejects a duplicate name', function () {
        Continent::factory()->create(['name' => 'Asia']);

        $response = $this->postJson(route('continents.store'), ['name' => 'Asia']);

        $response->assertJsonValidationErrors(['name' => 'There is already a continent with this name.']);
    });
});

describe('edit and update', function () {
    it('offers delete only for an empty continent', function () {
        $empty = Continent::factory()->create(['name' => 'Antarctica']);
        $full = Continent::factory()->create(['name' => 'Asia']);
        Country::factory()->for($full)->count(2)->create();

        $this->get(route('continents.edit', $empty))->assertSee('data-delete-action', false);
        $this->get(route('continents.edit', $full))
            ->assertDontSee('data-delete-action', false)
            ->assertSee('To delete Asia, first move or delete its 2 countries.');
    });

    it('renames a continent and keeps its own name valid', function () {
        $continent = Continent::factory()->create(['name' => 'Asia']);

        $this->putJson(route('continents.update', $continent), ['name' => 'Asia'])->assertOk();
        $this->putJson(route('continents.update', $continent), ['name' => 'East Asia'])->assertOk();

        expect($continent->fresh()->name)->toBe('East Asia');
    });
});

describe('destroy', function () {
    it('deletes an empty continent', function () {
        $continent = Continent::factory()->create();

        $this->deleteJson(route('continents.destroy', $continent))->assertOk();

        $this->assertModelMissing($continent);
    });

    it('refuses to delete a continent that still has countries', function () {
        $continent = Continent::factory()->create(['name' => 'Asia']);
        Country::factory()->for($continent)->create();

        $response = $this->deleteJson(route('continents.destroy', $continent));

        $response->assertJsonValidationErrors(['continent' => 'Move or delete the 1 country in Asia first.']);
        $this->assertModelExists($continent);
    });
});
