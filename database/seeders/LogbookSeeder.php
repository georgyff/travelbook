<?php

namespace Database\Seeders;

use App\Models\Continent;
use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds the logbook from the handoff sample data (placeholder content, editable in the UI).
 * Does nothing when the logbook already has continents, so it never overwrites the owner's data.
 */
class LogbookSeeder extends Seeder
{
    /**
     * Plural headings for the sample place types. Other types fall back to Str::plural().
     *
     * @var array<string, string>
     */
    private const PLURAL_NAMES = [
        'City' => 'Cities',
        'Village' => 'Villages',
        'Nature' => 'Nature',
        'Monument' => 'Monuments',
    ];

    public function run(): void
    {
        if (Continent::query()->exists()) {
            return;
        }

        /** @var array{placeTypes: list<string>, continents: list<array{id: string, name: string, order: int}>, regions: list<array{id: string, name: string, order: int, countries: list<string>}>, countries: list<array{id: string, name: string, continentId: string, order: int, status: string, history: string}>, places: list<array{id: string, countryId: string, name: string, type: string, isCapital: bool, status: string, shortHistory: string, facts: list<string>}>} $data */
        $data = json_decode(file_get_contents(database_path('seeders/data/logbook-sample.json')), true, flags: JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($data): void {
            $placeTypeIds = [];
            foreach ($data['placeTypes'] as $position => $name) {
                $placeTypeIds[$name] = PlaceType::create([
                    'name' => $name,
                    'plural_name' => self::PLURAL_NAMES[$name] ?? Str::plural($name),
                    'position' => $position + 1,
                ])->id;
            }

            $continentIds = [];
            foreach ($data['continents'] as $continent) {
                $continentIds[$continent['id']] = Continent::create([
                    'name' => $continent['name'],
                    'position' => $continent['order'],
                ])->id;
            }

            $countryIds = [];
            foreach ($data['countries'] as $country) {
                $countryIds[$country['id']] = Country::create([
                    'continent_id' => $continentIds[$country['continentId']],
                    'name' => $country['name'],
                    'status' => $country['status'],
                    'history' => $country['history'] !== '' ? $country['history'] : null,
                    'position' => $country['order'],
                ])->id;
            }

            foreach ($data['places'] as $place) {
                Place::create([
                    'country_id' => $countryIds[$place['countryId']],
                    'place_type_id' => $placeTypeIds[$place['type']],
                    'name' => $place['name'],
                    'is_capital' => $place['isCapital'],
                    'status' => $place['status'],
                    'short_history' => $place['shortHistory'] !== '' ? $place['shortHistory'] : null,
                    'facts' => $place['facts'],
                ]);
            }

            foreach ($data['regions'] as $regionData) {
                $region = Region::create(['name' => $regionData['name'], 'position' => $regionData['order']]);

                foreach ($regionData['countries'] as $position => $name) {
                    $region->members()->create(['name' => $name, 'position' => $position + 1]);
                }
            }
        });
    }
}
