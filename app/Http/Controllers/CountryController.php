<?php

namespace App\Http\Controllers;

use App\Enums\CompletionStatus;
use App\Http\Requests\CountryRequest;
use App\Models\Continent;
use App\Models\Country;
use App\Models\RegionCountry;
use App\Services\LogbookProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function create(Request $request): View
    {
        return view('logbook.forms.country', [
            'country' => new Country([
                'continent_id' => $request->integer('continent') ?: null,
                'status' => CompletionStatus::NotCompleted,
            ]),
            'continents' => Continent::query()->orderBy('position')->orderBy('id')->get(),
        ]);
    }

    public function store(CountryRequest $request): JsonResponse|RedirectResponse
    {
        $country = Country::create([
            ...$request->validated(),
            'position' => $this->nextPosition((int) $request->validated('continent_id')),
        ]);

        return $this->finishedAt(route('countries.show', $country));
    }

    public function edit(Country $country): View
    {
        return view('logbook.forms.country', [
            'country' => $country->loadCount('places'),
            'continents' => Continent::query()->orderBy('position')->orderBy('id')->get(),
        ]);
    }

    /**
     * Save the country. A country moved to another continent goes to the end of its list, and a
     * renamed country keeps its place in every region that listed it under the old name.
     */
    public function update(CountryRequest $request, Country $country): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($request, $country): void {
            $previousName = $country->name;
            $country->fill($request->validated());

            if ($country->isDirty('continent_id')) {
                $country->position = $this->nextPosition((int) $country->continent_id);
            }

            $country->save();

            if ($country->wasChanged('name')) {
                $this->renameRegionMembers($previousName, $country->name);
            }
        });

        return $this->finishedAt(route('countries.show', $country));
    }

    /**
     * Delete the country and its places. Regions keep the name, which then counts as not completed.
     */
    public function destroy(Country $country): JsonResponse|RedirectResponse
    {
        $country->delete();

        return $this->finishedAt(route('logbook'));
    }

    private function nextPosition(int $continentId): int
    {
        return (int) Country::query()->where('continent_id', $continentId)->max('position') + 1;
    }

    private function renameRegionMembers(string $previousName, string $newName): void
    {
        $previous = LogbookProgress::normalizeName($previousName);
        $new = LogbookProgress::normalizeName($newName);

        $members = RegionCountry::query()->get();
        $regionsWithNewName = $members
            ->filter(fn (RegionCountry $member): bool => LogbookProgress::normalizeName($member->name) === $new)
            ->pluck('region_id')
            ->flip();

        $members
            ->filter(fn (RegionCountry $member): bool => LogbookProgress::normalizeName($member->name) === $previous)
            ->each(function (RegionCountry $member) use ($regionsWithNewName, $newName): void {
                if ($regionsWithNewName->has($member->region_id)) {
                    $member->delete();

                    return;
                }

                $member->update(['name' => $newName]);
            });
    }
}
