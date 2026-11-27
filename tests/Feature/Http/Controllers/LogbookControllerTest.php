<?php

use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;
use App\Models\Region;

it('opens on the first country of the first region in index order', function () {
    $later = Region::factory()->create(['name' => 'Europe', 'position' => 2]);
    $first = Region::factory()->create(['name' => 'Asia', 'position' => 1]);
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

    $response->assertSee('<p class="country-head__desc">Three places recorded.</p>', false);
    $response->assertDontSee('Capital, Phnom Penh');
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

    $response->assertSeeInOrder(['Capital</h3>', 'Phnom Penh', 'Cities</h3>', 'Kampot', 'Monuments</h3>', 'Angkor Wat', 'Villages</h3>', 'Kep'], false);
});

it('lists several capitals under one Capitals heading, in their saved order', function () {
    $country = Country::factory()->create();
    Place::factory()->for($country)->capital()->create(['name' => 'The Hague', 'position' => 2]);
    Place::factory()->for($country)->capital()->create(['name' => 'Amsterdam', 'position' => 1]);

    $response = $this->get(route('countries.show', $country));

    $response->assertSeeInOrder(['Capitals</h3>', 'Amsterdam', 'The Hague'], false);
    $response->assertDontSee('Capital</h3>', false);
});

it('opens the place detail named in the query string', function () {
    $country = Country::factory()->create();
    $place = Place::factory()->for($country)->create();
    $other = Place::factory()->for($country)->create();

    $response = $this->get(route('countries.show', [$country, 'place' => $place->id]));

    $response->assertSee('data-place-toggle="'.$place->id.'" aria-controls="place-'.$place->id.'" aria-pressed="true"', false);
    $response->assertSee('data-place-detail="'.$other->id.'"  hidden', false);
});

it('shows times visited and pictures in the place detail, or a dash when not filled in', function () {
    $country = Country::factory()->create();
    $visited = Place::factory()->for($country)->create(['times_visited' => 3, 'pictures' => 1240]);
    Place::factory()->for($country)->create();

    $this->get(route('countries.show', [$country, 'place' => $visited->id]))
        ->assertOk()
        ->assertSeeInOrder(['times visited', '3', 'pictures', '1,240'], false)
        ->assertSee('<span class="sr-only">none</span>', false);
});

it('shows region, world and place-type totals', function () {
    $city = PlaceType::factory()->create(['name' => 'City', 'plural_name' => 'Cities']);
    $baltics = Region::factory()->create(['name' => 'Baltics']);
    $latvia = Country::factory()->for($baltics)->completed()->create(['name' => 'Latvia']);
    Country::factory()->for($baltics)->partial()->create(['name' => 'Lithuania']);
    Country::factory()->for($baltics)->notCompleted()->create(['name' => 'Estonia']);
    Place::factory()->for($latvia)->for($city)->completed()->count(2)->create();
    Place::factory()->for($latvia)->for($city)->partial()->create();

    $response = $this->get(route('logbook'));

    $response->assertSeeInOrder(['the world', '<div class="world__n">1</div>', 'of 3 countries · 33%'], false);
    $response->assertSeeInOrder(['Baltics', '1 <span class="muted" aria-hidden="true">/</span><span class="sr-only">of</span> 3'], false);
    $response->assertSeeInOrder(['Cities', '2<span class="sr-only"> completed</span>', '1<span class="sr-only"> partially completed</span>', 'total </span>3'], false);
});

it('shows an icon for each place type in its group heading and in places by type', function () {
    $country = Country::factory()->create();
    $city = PlaceType::factory()->create(['name' => 'City', 'plural_name' => 'Cities']);
    $spa = PlaceType::factory()->create(['name' => 'Spa', 'plural_name' => 'Spas']);
    Place::factory()->for($country)->for($city)->capital()->create();
    Place::factory()->for($country)->for($city)->create();
    Place::factory()->for($country)->for($spa)->create();

    $response = $this->get(route('countries.show', $country));

    $response->assertSeeInOrder(['places by type', 'data-icon="city"', 'Cities', 'data-icon="place"', 'Spas'], false);
    $response->assertSeeInOrder(['data-icon="capital"', 'Capital</h3>', 'data-icon="city"', 'Cities</h3>', 'data-icon="place"', 'Spas</h3>'], false);
});

it('shows each region both in the Regions list and as a group in the index', function () {
    $region = Region::factory()->create(['name' => 'Southeast Asia']);

    $response = $this->get(route('logbook'));

    $response->assertSeeInOrder([
        'id="progress-regions"', 'data-id="'.$region->id.'" data-name="Southeast Asia"', '0 <span class="muted" aria-hidden="true">/</span><span class="sr-only">of</span> 0',
        'id="index-region-groups"', 'data-region-group="'.$region->id.'"', 'southeast asia', '0 countries',
    ], false);
});

it('shows the country flag in the index and above the country name', function () {
    $country = Country::factory()->create(['name' => 'Cambodia', 'flag_code' => 'KH']);

    $response = $this->get(route('countries.show', $country));

    $response->assertSee('<span class="flag" aria-hidden="true">'."\u{1F1F0}\u{1F1ED}".'</span>', false);
    $response->assertSee('<div class="flag flag--large" aria-hidden="true">'."\u{1F1F0}\u{1F1ED}".'</div>', false);
});

it('shows no flag for a country without a flag code', function () {
    $country = Country::factory()->create(['flag_code' => null]);

    $response = $this->get(route('countries.show', $country));

    $response->assertDontSee('class="flag', false);
});

it('labels the country page with its region', function () {
    $country = Country::factory()->for(Region::factory()->create(['name' => 'Baltics']))->create();

    $response = $this->get(route('countries.show', $country));

    $response->assertSee('<div class="sc muted country-head__region">baltics</div>', false);
});

it('prompts the owner to add content when the logbook is empty', function () {
    $response = $this->get(route('logbook'));

    $response->assertSee('Add a region, then a country, to begin.');
});

it('escapes owner-written text', function () {
    $country = Country::factory()->create(['history' => '<script>alert(1)</script>']);

    $response = $this->get(route('countries.show', $country));

    $response->assertDontSee('<script>alert(1)</script>', false);
    $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

it('formats bold and italic text in a place history and its facts', function () {
    $place = Place::factory()->create([
        'short_history' => "Built in the **12th century**.\nRestored *twice*.",
        'facts' => ['The **largest** temple', 'Seen from *space*'],
    ]);

    $response = $this->get(route('countries.show', [$place->country_id, 'place' => $place->id]));

    $response->assertSee("<p>Built in the <strong>12th century</strong>.\nRestored <em>twice</em>.</p>", false);
    $response->assertSee('<li>The <strong>largest</strong> temple</li>', false);
    $response->assertSee('<li>Seen from <em>space</em></li>', false);
});

it('formats bold and italic text in the country history', function () {
    $country = Country::factory()->create(['history' => "The **Khmer Empire** rose here.\n\nSee *Angkor*."]);

    $response = $this->get(route('countries.show', $country));

    $response->assertSee("<p class=\"history\">The <strong>Khmer Empire</strong> rose here.\n\nSee <em>Angkor</em>.</p>", false);
});

it('escapes HTML and drops unsafe links in formatted place text', function () {
    $place = Place::factory()->create([
        'short_history' => '<img src=x onerror=alert(1)> [click](javascript:alert(1))',
        'facts' => ['<script>alert(1)</script>'],
    ]);

    $response = $this->get(route('countries.show', [$place->country_id, 'place' => $place->id]));

    $response->assertDontSee('<img src=x', false);
    $response->assertDontSee('href="javascript:', false);
    $response->assertDontSee('<script>alert(1)</script>', false);
    $response->assertSee('&lt;img src=x onerror=alert(1)&gt;', false);
});

it('returns 404 for a country that does not exist', function () {
    $this->get('/countries/999')->assertNotFound();
});
