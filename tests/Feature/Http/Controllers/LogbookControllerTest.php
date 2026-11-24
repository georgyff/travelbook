<?php

use App\Models\Continent;
use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;
use App\Models\Region;

it('opens on the first country of the first continent in index order', function () {
    $later = Continent::factory()->create(['name' => 'Europe', 'position' => 2]);
    $first = Continent::factory()->create(['name' => 'Asia', 'position' => 1]);
    Country::factory()->for($later)->create(['name' => 'Latvia', 'position' => 1]);
    Country::factory()->for($first)->create(['name' => 'Japan', 'position' => 2]);
    Country::factory()->for($first)->create(['name' => 'Cambodia', 'position' => 1]);

    $response = $this->get(route('logbook'));

    $response->assertSee('<h1 id="country-name" tabindex="-1">Cambodia</h1>', false);
});

it('renders the selected country with its descriptor and place counts', function () {
    $country = Country::factory()->partial()->create(['name' => 'Cambodia']);
    $city = PlaceType::factory()->create(['name' => 'City', 'plural_name' => 'Cities']);
    Place::factory()->for($country)->for($city)->capital()->completed()->create(['name' => 'Phnom Penh']);
    Place::factory()->for($country)->for($city)->partial()->create();
    Place::factory()->for($country)->for($city)->notCompleted()->create();

    $response = $this->get(route('countries.show', $country));

    $response->assertSee('Capital, Phnom Penh. Three places recorded.');
    $response->assertSee('1 completed · 1 partial · 1 not yet');
    $response->assertSee('Partially completed');
});

it('groups places with the capital first, then types by plural heading', function () {
    $country = Country::factory()->create();
    $village = PlaceType::factory()->create(['name' => 'Village', 'plural_name' => 'Villages', 'position' => 1]);
    $city = PlaceType::factory()->create(['name' => 'City', 'plural_name' => 'Cities', 'position' => 2]);
    $monument = PlaceType::factory()->create(['name' => 'Monument', 'plural_name' => 'Monuments', 'position' => 3]);
    Place::factory()->for($country)->for($village)->create(['name' => 'Kep']);
    Place::factory()->for($country)->for($monument)->create(['name' => 'Angkor Wat']);
    Place::factory()->for($country)->for($city)->create(['name' => 'Kampot']);
    Place::factory()->for($country)->for($city)->capital()->create(['name' => 'Phnom Penh']);

    $response = $this->get(route('countries.show', $country));

    $response->assertSeeInOrder(['>Capital</h3>', 'Phnom Penh', '>Cities</h3>', 'Kampot', '>Monuments</h3>', 'Angkor Wat', '>Villages</h3>', 'Kep'], false);
});

it('opens the place detail named in the query string', function () {
    $country = Country::factory()->create();
    $place = Place::factory()->for($country)->create();
    $other = Place::factory()->for($country)->create();

    $response = $this->get(route('countries.show', [$country, 'place' => $place->id]));

    $response->assertSee('data-place-toggle="'.$place->id.'" aria-controls="place-'.$place->id.'" aria-pressed="true"', false);
    $response->assertSee('data-place-detail="'.$other->id.'"  hidden', false);
});

it('shows region, world and place-type totals', function () {
    $city = PlaceType::factory()->create(['name' => 'City', 'plural_name' => 'Cities']);
    $latvia = Country::factory()->completed()->create(['name' => 'Latvia']);
    Country::factory()->partial()->create(['name' => 'Lithuania']);
    Region::factory()->withMembers(['Latvia', 'Lithuania', 'Estonia'])->create(['name' => 'Baltics']);
    Place::factory()->for($latvia)->for($city)->completed()->count(2)->create();
    Place::factory()->for($latvia)->for($city)->partial()->create();

    $response = $this->get(route('logbook'));

    $response->assertSeeInOrder(['the world', '<div class="world__n">1</div>', 'of 3 countries · 33%'], false);
    $response->assertSeeInOrder(['Baltics', '1 <span class="muted italic">of</span> 3'], false);
    $response->assertSeeInOrder(['Cities', '2<span class="sr-only"> completed</span>', '1<span class="sr-only"> partially completed</span>', 'total </span>3'], false);
});

it('prompts the owner to add content when the logbook is empty', function () {
    $response = $this->get(route('logbook'));

    $response->assertSee('Add a continent, then a country, to begin.');
});

it('escapes owner-written text', function () {
    $country = Country::factory()->create(['history' => '<script>alert(1)</script>']);

    $response = $this->get(route('countries.show', $country));

    $response->assertDontSee('<script>alert(1)</script>', false);
    $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

it('returns 404 for a country that does not exist', function () {
    $this->get('/countries/999')->assertNotFound();
});
