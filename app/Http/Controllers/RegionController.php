<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegionRequest;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Regions group the country index and make up the Regions progress list.
 */
class RegionController extends Controller
{
    public function create(): View
    {
        return view('logbook.forms.region', ['region' => new Region]);
    }

    public function store(RegionRequest $request): JsonResponse|RedirectResponse
    {
        Region::create([
            'name' => $request->validated('name'),
            'position' => (int) Region::query()->max('position') + 1,
        ]);

        return $this->finishedAt(url()->previous(route('logbook')));
    }

    public function edit(Region $region): View
    {
        return view('logbook.forms.region', ['region' => $region->loadCount('countries')]);
    }

    public function update(RegionRequest $request, Region $region): JsonResponse|RedirectResponse
    {
        $region->update($request->validated());

        return $this->finishedAt(url()->previous(route('logbook')));
    }

    /**
     * A region can only be deleted once it is empty, so no country is lost by accident.
     *
     * @throws ValidationException
     */
    public function destroy(Region $region): JsonResponse|RedirectResponse
    {
        $countryCount = $region->countries()->count();

        if ($countryCount > 0) {
            throw ValidationException::withMessages([
                'region' => "Move or delete the {$countryCount} ".Str::plural('country', $countryCount)." in {$region->name} first.",
            ]);
        }

        $region->delete();

        return $this->finishedAt(url()->previous(route('logbook')));
    }
}
