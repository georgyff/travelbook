<?php

namespace App\Http\Controllers;

use App\Enums\CompletionStatus;
use App\Http\Requests\CountryRequest;
use App\Models\Country;
use App\Models\Region;
use App\Services\CountryFlags;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function create(Request $request): View
    {
        return view('logbook.forms.country', [
            'country' => new Country([
                'region_id' => $request->integer('region') ?: null,
                'status' => CompletionStatus::NotCompleted,
            ]),
            'regions' => Region::query()->orderBy('position')->orderBy('id')->get(),
            'flags' => new CountryFlags,
        ]);
    }

    public function store(CountryRequest $request): JsonResponse|RedirectResponse
    {
        $country = Country::create([
            ...$request->validated(),
            'position' => $this->nextPosition((int) $request->validated('region_id')),
        ]);

        return $this->finishedAt(route('countries.show', $country));
    }

    public function edit(Country $country): View
    {
        return view('logbook.forms.country', [
            'country' => $country->loadCount('places'),
            'regions' => Region::query()->orderBy('position')->orderBy('id')->get(),
            'flags' => new CountryFlags,
        ]);
    }

    /**
     * Save the country. A country moved to another region goes to the end of that region's list.
     */
    public function update(CountryRequest $request, Country $country): JsonResponse|RedirectResponse
    {
        $country->fill($request->validated());

        if ($country->isDirty('region_id')) {
            $country->position = $this->nextPosition((int) $country->region_id);
        }

        $country->save();

        return $this->finishedAt(route('countries.show', $country));
    }

    /**
     * Delete the country and its places.
     */
    public function destroy(Country $country): JsonResponse|RedirectResponse
    {
        $country->delete();

        return $this->finishedAt(route('logbook'));
    }

    private function nextPosition(int $regionId): int
    {
        return (int) Country::query()->where('region_id', $regionId)->max('position') + 1;
    }
}
