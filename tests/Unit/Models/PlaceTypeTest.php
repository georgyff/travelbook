<?php

use App\Models\PlaceType;

it('picks the icon from the type name, singular or plural', function (string $name, string $pluralName, string $icon) {
    expect((new PlaceType(['name' => $name, 'plural_name' => $pluralName]))->icon())->toBe($icon);
})->with([
    'city' => ['City', 'Cities', 'city'],
    'town counts as city' => ['Town', 'Towns', 'city'],
    'village' => ['Village', 'Villages', 'village'],
    'nature' => ['Nature', 'Nature', 'nature'],
    'monument' => ['Monument', 'Monuments', 'monument'],
    'plural name only, any case' => ['Ancient site', ' MONUMENTS ', 'monument'],
    'unknown type gets a map pin' => ['Spa', 'Spas', 'place'],
]);
