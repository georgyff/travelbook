<?php

namespace App\Http\Controllers;

use App\Enums\TripColumn;
use App\Enums\TripStatus;
use App\Http\Requests\TripRequest;
use App\Models\Country;
use App\Models\Place;
use App\Models\Trip;
use App\Services\LogbookProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TripController extends Controller
{
    /**
     * The trips table, sorted by any column (?sort=cost&direction=desc). Newest first by default.
     */
    public function index(Request $request): View
    {
        $column = TripColumn::tryFrom((string) $request->query('sort')) ?? TripColumn::Dates;
        $descending = $request->query('direction', $request->has('sort') ? 'asc' : 'desc') === 'desc';

        $trips = Trip::query()->with('countries.region')->get();

        return view('trips.index', [
            'trips' => $column->sort($trips, $descending),
            'sortColumn' => $column,
            'descending' => $descending,
            'totals' => $this->totals($trips),
        ]);
    }

    public function show(Trip $trip): View
    {
        return view('trips.show', [
            'trip' => $trip->load(['countries.region', 'countries.places', 'stops']),
        ]);
    }

    public function create(): View
    {
        return view('trips.form', [
            'trip' => new Trip(['status' => TripStatus::Idea]),
            'countryNames' => $this->countryNames(),
            'placeNames' => $this->placeNames(),
        ]);
    }

    public function store(TripRequest $request): JsonResponse|RedirectResponse
    {
        $trip = DB::transaction(function () use ($request): Trip {
            $trip = Trip::create($request->tripAttributes());
            $this->syncCountries($trip, $request->countryIds());

            return $trip;
        });

        return $this->finishedAt(route('trips.show', $trip));
    }

    public function edit(Trip $trip): View
    {
        return view('trips.form', [
            'trip' => $trip->load('countries'),
            'countryNames' => $this->countryNames(),
            'placeNames' => $this->placeNames(),
        ]);
    }

    public function update(TripRequest $request, Trip $trip): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($request, $trip): void {
            $trip->update($request->tripAttributes());
            $this->syncCountries($trip, $request->countryIds());
        });

        return $this->finishedAt(route('trips.show', $trip));
    }

    public function destroy(Trip $trip): JsonResponse|RedirectResponse
    {
        $trip->delete();

        return $this->finishedAt(route('trips.index'));
    }

    /**
     * @param  list<int>  $countryIds
     */
    private function syncCountries(Trip $trip, array $countryIds): void
    {
        $trip->countries()->sync(
            collect($countryIds)->mapWithKeys(fn (int $id, int $index): array => [$id => ['position' => $index + 1]])->all(),
        );
    }

    /**
     * Every Logbook country name, for the countries field's suggestions.
     *
     * @return Collection<int, string>
     */
    private function countryNames(): Collection
    {
        return Country::query()->orderBy('name')->pluck('name');
    }

    /**
     * Every Logbook place name, each once, for the places visited field's suggestions.
     *
     * @return Collection<int, string>
     */
    private function placeNames(): Collection
    {
        return Place::query()->orderBy('name')->distinct()->pluck('name');
    }

    /**
     * The table's totals row. Countries and places are counted once however many trips went there.
     *
     * @param  Collection<int, Trip>  $trips
     * @return array{trips: int, countries: int, places: int, days: int, steps: int, km: float, cost: float, pictures: int}
     */
    private function totals(Collection $trips): array
    {
        return [
            'trips' => $trips->count(),
            'countries' => $trips->flatMap->countries->unique('id')->count(),
            'places' => $trips->flatMap(fn (Trip $trip): array => array_map(LogbookProgress::normalizeName(...), $trip->places_visited ?? []))->unique()->count(),
            'days' => (int) $trips->sum('days'),
            'steps' => (int) $trips->sum('walked_steps'),
            'km' => (float) $trips->sum('walked_km'),
            'cost' => (float) $trips->sum(fn (Trip $trip): float => $trip->totalCost() ?? 0),
            'pictures' => (int) $trips->sum('pictures'),
        ];
    }
}
