<?php

use App\Services\CountryFlags;

it('matches a country name to its ISO code', function (string $name, string $code) {
    expect((new CountryFlags)->codeFor($name))->toBe($code);
})->with([
    'plain name' => ['Cambodia', 'KH'],
    'ignores case and spaces' => ['  united kingdom ', 'GB'],
    'and for ampersand, ignoring accents' => ['São Tomé and Principe', 'ST'],
    'leading The' => ['The Gambia', 'GM'],
    'common alias' => ['Ivory Coast', 'CI'],
    'former name' => ['East Timor', 'TL'],
    'Congo, Kinshasa' => ['Democratic Republic of the Congo', 'CD'],
    'Congo, Brazzaville' => ['Republic of the Congo', 'CG'],
    'Kosovo' => ['Kosovo', 'XK'],
    'territory' => ['Faroe Islands', 'FO'],
    'current code, not retired CS or YU' => ['Serbia', 'RS'],
    'current code, not retired DD' => ['Germany', 'DE'],
    'current code, not retired YD' => ['Yemen', 'YE'],
    'current code, not retired NH' => ['Vanuatu', 'VU'],
    'current code, not retired RH' => ['Zimbabwe', 'ZW'],
]);

it('returns null for a name it cannot match', function () {
    expect((new CountryFlags)->codeFor('Atlantis'))->toBeNull();
});

it('builds the flag emoji from regional indicator letters', function () {
    expect((new CountryFlags)->emoji('kh'))->toBe("\u{1F1F0}\u{1F1ED}");
});

it('offers countries and territories but not groupings', function () {
    $options = (new CountryFlags)->options();

    expect($options)
        ->toHaveKey('KH', 'Cambodia')
        ->toHaveKey('XK', 'Kosovo')
        ->not->toHaveKey('EU')
        ->not->toHaveKey('UN')
        ->not->toHaveKey('CS')
        ->not->toHaveKey('SU');
});
