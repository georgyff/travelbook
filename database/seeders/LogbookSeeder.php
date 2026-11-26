<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;
use App\Models\Region;
use App\Services\CountryFlags;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds the logbook from the handoff sample data (placeholder content, editable in the UI).
 * Does nothing when the logbook already has regions, so it never overwrites the owner's data.
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
        if (Region::query()->exists()) {
            return;
        }

        /** @var array{placeTypes: list<string>, regions: list<array{id: string, name: string, order: int}>, countries: list<array{id: string, name: string, regionId: string, order: int, status: string, history: string}>, places: list<array{id: string, countryId: string, name: string, type: string, isCapital: bool, status: string, shortHistory: string, facts: list<string>}>} $data */
        $data = json_decode(file_get_contents(database_path('seeders/data/logbook-sample.json')), true, flags: JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($data): void {
            $placeTypeIds = [];
            foreach ($data['placeTypes'] as $position => $name) {
                $placeTypeIds[$name] = PlaceType::firstOrCreate(['name' => $name], [
                    'plural_name' => self::PLURAL_NAMES[$name] ?? Str::plural($name),
                    'position' => $position + 1,
                ])->id;
            }

            $regionIds = [];
            foreach ($data['regions'] as $region) {
                $regionIds[$region['id']] = Region::create([
                    'name' => $region['name'],
                    'position' => $region['order'],
                ])->id;
            }

            $flags = new CountryFlags;
            $countryIds = [];
            foreach ($data['countries'] as $country) {
                $countryIds[$country['id']] = Country::create([
                    'region_id' => $regionIds[$country['regionId']],
                    'name' => $country['name'],
                    'flag_code' => $flags->codeFor($country['name']),
                    'status' => $country['status'],
                    'history' => $country['history'] !== '' ? $country['history'] : null,
                    'position' => $country['order'],
                ])->id;
            }

            $nextPositions = [];
            foreach ($data['places'] as $place) {
                $group = $place['countryId'].'-'.($place['isCapital'] ? 'capital' : $place['type']);
                $nextPositions[$group] = ($nextPositions[$group] ?? 0) + 1;

                Place::create([
                    'country_id' => $countryIds[$place['countryId']],
                    'place_type_id' => $placeTypeIds[$place['type']],
                    'name' => $place['name'],
                    'is_capital' => $place['isCapital'],
                    'position' => $nextPositions[$group],
                    'status' => $place['status'],
                    'short_history' => $place['shortHistory'] !== '' ? $place['shortHistory'] : null,
                    'facts' => $place['facts'],
                ]);
            }
        });
    }
}
