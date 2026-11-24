<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegionRequest;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegionController extends Controller
{
    public function create(): View
    {
        return view('logbook.forms.region', [
            'region' => new Region,
            'memberNames' => [],
            'countryNames' => $this->countryNames(),
        ]);
    }

    public function store(RegionRequest $request): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $region = Region::create([
                'name' => $request->validated('name'),
                'position' => (int) Region::query()->max('position') + 1,
            ]);

            $this->syncMembers($region, $request->validated('countries', []));
        });

        return $this->finishedAt(url()->previous(route('logbook')));
    }

    public function edit(Region $region): View
    {
        return view('logbook.forms.region', [
            'region' => $region,
            'memberNames' => $region->members->pluck('name')->all(),
            'countryNames' => $this->countryNames(),
        ]);
    }

    public function update(RegionRequest $request, Region $region): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($request, $region): void {
            $region->update(['name' => $request->validated('name')]);
            $this->syncMembers($region, $request->validated('countries', []));
        });

        return $this->finishedAt(url()->previous(route('logbook')));
    }

    public function destroy(Region $region): JsonResponse|RedirectResponse
    {
        $region->delete();

        return $this->finishedAt(url()->previous(route('logbook')));
    }

    /**
     * Replace the region's country list with these names, keeping their order.
     *
     * @param  list<string>  $names
     */
    private function syncMembers(Region $region, array $names): void
    {
        $region->members()->delete();

        foreach (array_values($names) as $index => $name) {
            $region->members()->create(['name' => $name, 'position' => $index + 1]);
        }
    }

    /**
     * Logbook country names, offered as suggestions in the region form.
     *
     * @return list<string>
     */
    private function countryNames(): array
    {
        return Country::query()->orderBy('name')->pluck('name')->all();
    }
}
