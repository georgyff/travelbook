<?php

use App\Enums\TripStatus;
use App\Enums\TripType;
use App\Models\Country;
use App\Models\Place;
use App\Models\Region;
use App\Models\Trip;

describe('index', function () {
    it('lists trips newest first, with derived totals', function () {
        $older = Trip::factory()->done()->create(['name' => 'Baltics', 'starts_on' => '2025-06-01', 'ends_on' => '2025-06-09']);
        Trip::factory()->done()->create(['name' => 'Benelux']);
        Trip::factory()->create(['name' => 'Someday Japan']);
        $latvia = Country::factory()->create(['name' => 'Latvia']);
        $older->countries()->attach($latvia, ['position' => 1]);

        $response = $this->get(route('trips.index'));

        $response->assertOk();
        $response->assertSeeInOrder(['Benelux', 'Baltics', 'Someday Japan']);
        $response->assertSee('3 trips · 1 country · 20 days');
        $response->assertSeeInOrder(['Total', '1 country', '20', '364,000', '263', '€2,620', '824'], false);
        $response->assertSee('aria-sort="descending"', false);
    });

    it('sorts by any column, keeping trips without a value last', function () {
        Trip::factory()->create(['name' => 'Cheap', 'cost_in_travel' => 100]);
        Trip::factory()->create(['name' => 'Dear', 'cost_travel' => 900, 'cost_in_travel' => 100]);
        Trip::factory()->create(['name' => 'Unknown']);

        $this->get(route('trips.index', ['sort' => 'cost']))
            ->assertSeeInOrder(['Cheap', 'Dear', 'Unknown']);
        $this->get(route('trips.index', ['sort' => 'cost', 'direction' => 'desc']))
            ->assertSeeInOrder(['Dear', 'Cheap', 'Unknown'])
            ->assertSee('href="'.e(route('trips.index', ['sort' => 'cost', 'direction' => 'asc'])).'"', false);
    });

    it('sorts by status in idea-to-done order', function () {
        Trip::factory()->create(['name' => 'Finished', 'status' => TripStatus::Done]);
        Trip::factory()->create(['name' => 'Dreaming', 'status' => TripStatus::Idea]);
        Trip::factory()->create(['name' => 'Underway', 'status' => TripStatus::Ongoing]);

        $this->get(route('trips.index', ['sort' => 'status']))
            ->assertSeeInOrder(['Dreaming', 'Underway', 'Finished']);
    });

    it('says when there are no trips', function () {
        $this->get(route('trips.index'))->assertSee('No trips yet.');
    });
});

describe('show', function () {
    it('shows the cost breakdown, total and average per day', function () {
        $trip = Trip::factory()->done()->create(['name' => 'Benelux']);
        $europe = Region::factory()->create(['name' => 'Europe']);
        $trip->countries()->attach([
            Country::factory()->for($europe)->create(['name' => 'Netherlands', 'flag_code' => 'NL'])->id => ['position' => 1],
            Country::factory()->for($europe)->create(['name' => 'Belgium', 'flag_code' => 'BE'])->id => ['position' => 2],
        ]);

        $response = $this->get(route('trips.show', $trip));

        $response->assertOk();
        $response->assertSee('<h1 id="trip-name" tabindex="-1">Benelux</h1>', false);
        $response->assertSee('3 → 12 May 2026 · 10 days');
        $response->assertSeeInOrder(['Netherlands', 'Belgium', 'Continent', 'Europe'], false);
        $response->assertSeeInOrder(['Inter-country / city travel', '€320', 'Housing', '€540', 'In-travel', '€450', 'Total', '€1,310'], false);
        $response->assertSeeInOrder(['avg eur per day', '€45', 'in-travel €450 ÷ 10 days'], false);
    });

    it('explains a missing average per day', function () {
        $trip = Trip::factory()->create(['cost_in_travel' => 300]);

        $this->get(route('trips.show', $trip))->assertSee('Fill in In-travel and Days to work this out.');
    });
});

describe('create, update and delete', function () {
    it('adds a trip with its countries in the order given', function () {
        $netherlands = Country::factory()->create(['name' => 'Netherlands']);
        $belgium = Country::factory()->create(['name' => 'Belgium']);

        $response = $this->postJson(route('trips.store'), [
            'name' => ' Benelux ',
            'status' => 'booked',
            'type' => 'couple',
            'starts_on' => '2026-05-03',
            'ends_on' => '2026-05-12',
            'days' => '10',
            'walked_km' => '12.5',
            'cost_in_travel' => '450.50',
            'countries' => ['belgium', '', ' Netherlands '],
        ]);

        $trip = Trip::query()->sole();
        $response->assertOk()->assertJson(['redirect' => route('trips.show', $trip)]);
        expect($trip)
            ->name->toBe('Benelux')
            ->status->toBe(TripStatus::Booked)
            ->type->toBe(TripType::Couple)
            ->days->toBe(10)
            ->walked_km->toBe(12.5)
            ->cost_in_travel->toBe(450.5)
            ->cost_travel->toBeNull();
        expect($trip->countries->pluck('id')->all())->toBe([$belgium->id, $netherlands->id]);
    });

    it('rejects countries that are not in the Logbook or are listed twice', function () {
        Country::factory()->create(['name' => 'Belgium']);

        $response = $this->postJson(route('trips.store'), [
            'name' => 'Trip',
            'status' => 'idea',
            'countries' => ['Belgium', 'Atlantis', 'belgium'],
        ]);

        $response->assertJsonValidationErrors([
            'countries.1' => '“Atlantis” isn’t in the Logbook.',
            'countries.2' => 'This country is already on the trip.',
        ]);
        $this->assertDatabaseCount('trips', 0);
    });

    it('rejects an end date before the start and negative figures', function () {
        $response = $this->postJson(route('trips.store'), [
            'name' => 'Trip',
            'status' => 'idea',
            'starts_on' => '2026-05-10',
            'ends_on' => '2026-05-01',
            'cost_housing' => '-5',
        ]);

        $response->assertJsonValidationErrors([
            'ends_on' => 'The end date can’t be before the start date.',
            'cost_housing' => 'Use zero or more.',
        ]);
    });

    it('updates a trip and replaces its countries', function () {
        $trip = Trip::factory()->done()->create();
        $trip->countries()->attach(Country::factory()->create(), ['position' => 1]);
        $spain = Country::factory()->create(['name' => 'Spain']);

        $response = $this->putJson(route('trips.update', $trip), [
            'name' => 'Iberia',
            'status' => 'done',
            'type' => '',
            'countries' => ['Spain'],
        ]);

        $response->assertOk()->assertJson(['redirect' => route('trips.show', $trip)]);
        expect($trip->fresh())
            ->name->toBe('Iberia')
            ->type->toBeNull()
            ->days->toBeNull();
        expect($trip->fresh()->countries->pluck('id')->all())->toBe([$spain->id]);
    });

    it('deletes a trip and returns to the table', function () {
        $trip = Trip::factory()->create();
        $trip->countries()->attach(Country::factory()->create(), ['position' => 1]);

        $this->deleteJson(route('trips.destroy', $trip))
            ->assertOk()
            ->assertJson(['redirect' => route('trips.index')]);

        $this->assertModelMissing($trip);
        $this->assertDatabaseCount('country_trip', 0);
    });

    it('offers every Logbook country as a suggestion in the form', function () {
        Country::factory()->create(['name' => 'Belgium']);

        $this->get(route('trips.create'))
            ->assertOk()
            ->assertSee('<option value="Belgium"></option>', false);
    });
});

describe('places visited', function () {
    it('saves the places typed, in order, without blanks', function () {
        $this->postJson(route('trips.store'), [
            'name' => 'Italia',
            'status' => 'done',
            'places_visited' => [' Pisa ', '', 'Florence', 'Lucca'],
        ])->assertOk();

        expect(Trip::query()->sole()->places_visited)->toBe(['Pisa', 'Florence', 'Lucca']);
    });

    it('rejects a place listed twice', function () {
        $this->postJson(route('trips.store'), [
            'name' => 'Italia',
            'status' => 'done',
            'places_visited' => ['Pisa', 'pisa'],
        ])->assertJsonValidationErrors(['places_visited.1' => 'This place is already listed.']);
    });

    it('clears the places when the list is emptied', function () {
        $trip = Trip::factory()->create(['places_visited' => ['Pisa']]);

        $this->putJson(route('trips.update', $trip), ['name' => 'Italia', 'status' => 'done'])->assertOk();

        expect($trip->fresh()->places_visited)->toBe([]);
    });

    it('links places that are in the Logbook for the trip’s countries, and lists the rest as text', function () {
        $italy = Country::factory()->create(['name' => 'Italy']);
        $pisa = Place::factory()->for($italy)->create(['name' => 'Pisa']);
        Place::factory()->create(['name' => 'Lucca']);
        $trip = Trip::factory()->create(['name' => 'Italia', 'places_visited' => ['pisa', 'Florence', 'Lucca']]);
        $trip->countries()->attach($italy, ['position' => 1]);

        $response = $this->get(route('trips.show', $trip));

        $response->assertSeeInOrder(['Places visited', '<a class="trip-country" href="'.route('countries.show', [$italy, 'place' => $pisa->id]).'">pisa</a>', '<span class="trip-country">Florence</span>', '<span class="trip-country">Lucca</span>'], false);
    });

    it('shows them as a sortable column in the trips table, counted once in the total', function () {
        Trip::factory()->create(['name' => 'Italia', 'places_visited' => ['Pisa', 'Florence', 'Lucca']]);
        Trip::factory()->create(['name' => 'Tuscany again', 'places_visited' => ['Florence']]);
        Trip::factory()->create(['name' => 'Nowhere yet']);

        $this->get(route('trips.index', ['sort' => 'places']))
            ->assertSeeInOrder(['Places visited', 'Tuscany again', 'Florence', 'Italia', 'Pisa, Florence, Lucca', 'Nowhere yet', '3 places'], false);
    });

    it('suggests Logbook place names in the form', function () {
        Place::factory()->create(['name' => 'Pisa']);

        $this->get(route('trips.create'))->assertSee('<option value="Pisa"></option>', false);
    });
});

it('links Logbook and Trips from the masthead', function () {
    $this->get(route('logbook'))->assertSee('<a href="'.route('trips.index').'" >Trips</a>', false);
    $this->get(route('trips.index'))->assertSee('<a href="'.route('trips.index').'"  aria-current="page" >Trips</a>', false);
});
