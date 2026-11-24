<?php

namespace App\Http\Controllers;

use App\Enums\CompletionStatus;
use App\Models\Continent;
use App\Models\Country;
use App\Models\Place;
use App\Models\PlaceType;
use App\Models\Region;
use App\Services\LogbookProgress;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class LogbookController extends Controller
{
    /**
     * @var list<string>
     */
    private const NUMBER_WORDS = ['No', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve'];

    /**
     * The logbook opened on the first country in the index.
     */
    public function index(Request $request, LogbookProgress $progress): View
    {
        return $this->render($request, $progress, null);
    }

    /**
     * The logbook opened on one country.
     */
    public function show(Request $request, LogbookProgress $progress, Country $country): View
    {
        return $this->render($request, $progress, $country);
    }

    private function render(Request $request, LogbookProgress $progress, ?Country $country): View
    {
        $continents = Continent::query()
            ->orderBy('position')
            ->orderBy('id')
            ->with(['countries' => fn ($query) => $query->withCount('places')])
            ->get();

        $regions = Region::query()->orderBy('position')->orderBy('id')->with('members')->get();
        $placeTypes = PlaceType::query()->orderBy('position')->orderBy('id')->get();

        $country ??= $continents->flatMap->countries->first();
        $country?->load(['continent', 'places.placeType']);

        $places = $country?->places ?? new EloquentCollection;
        $selectedPlace = $places->firstWhere('id', $request->integer('place'));

        return view('logbook.show', [
            'continents' => $continents,
            'countryProgress' => $progress->countryProgress($regions),
            'placesByType' => $progress->placesByType($placeTypes),
            'country' => $country,
            'placeGroups' => $this->groupPlaces($places, $placeTypes),
            'placeCounts' => [
                'completed' => $places->where('status', CompletionStatus::Completed)->count(),
                'partial' => $places->where('status', CompletionStatus::Partial)->count(),
                'not_completed' => $places->where('status', CompletionStatus::NotCompleted)->count(),
            ],
            'descriptor' => $country ? $this->describe($places) : null,
            'selectedPlace' => $selectedPlace,
        ]);
    }

    /**
     * Group a country's places: the capital first, then each type ordered by its plural heading
     * (Cities, Monuments, Nature, Villages). Empty groups are left out.
     *
     * @param  EloquentCollection<int, Place>  $places
     * @param  EloquentCollection<int, PlaceType>  $placeTypes
     * @return list<array{key: string, heading: string, places: Collection<int, Place>}>
     */
    private function groupPlaces(EloquentCollection $places, EloquentCollection $placeTypes): array
    {
        $groups = [[
            'key' => 'capital',
            'heading' => 'Capital',
            'places' => $places->where('is_capital', true)->values(),
        ]];

        foreach ($placeTypes->sortBy('plural_name', SORT_NATURAL | SORT_FLAG_CASE) as $placeType) {
            $groups[] = [
                'key' => 'type-'.$placeType->id,
                'heading' => $placeType->plural_name,
                'places' => $places->where('is_capital', false)->where('place_type_id', $placeType->id)->values(),
            ];
        }

        return array_values(array_filter($groups, fn (array $group): bool => $group['places']->isNotEmpty()));
    }

    /**
     * The italic line under the country name, e.g. "Capital, Phnom Penh. Seven places recorded."
     *
     * @param  EloquentCollection<int, Place>  $places
     */
    private function describe(EloquentCollection $places): string
    {
        $capital = $places->firstWhere('is_capital', true);
        $count = $places->count();
        $countWord = self::NUMBER_WORDS[$count] ?? (string) $count;

        return ($capital ? "Capital, {$capital->name}. " : '')
            .$countWord.' '.($count === 1 ? 'place' : 'places').' recorded.';
    }
}
