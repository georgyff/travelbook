<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContinentRequest;
use App\Models\Continent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContinentController extends Controller
{
    public function create(): View
    {
        return view('logbook.forms.continent', ['continent' => new Continent]);
    }

    public function store(ContinentRequest $request): JsonResponse|RedirectResponse
    {
        Continent::create([
            'name' => $request->validated('name'),
            'position' => (int) Continent::query()->max('position') + 1,
        ]);

        return $this->finishedAt(url()->previous(route('logbook')));
    }

    public function edit(Continent $continent): View
    {
        return view('logbook.forms.continent', [
            'continent' => $continent->loadCount('countries'),
        ]);
    }

    public function update(ContinentRequest $request, Continent $continent): JsonResponse|RedirectResponse
    {
        $continent->update($request->validated());

        return $this->finishedAt(url()->previous(route('logbook')));
    }

    /**
     * A continent can only be deleted once it is empty, so no country is lost by accident.
     *
     * @throws ValidationException
     */
    public function destroy(Continent $continent): JsonResponse|RedirectResponse
    {
        $countryCount = $continent->countries()->count();

        if ($countryCount > 0) {
            throw ValidationException::withMessages([
                'continent' => "Move or delete the {$countryCount} ".Str::plural('country', $countryCount)." in {$continent->name} first.",
            ]);
        }

        $continent->delete();

        return $this->finishedAt(url()->previous(route('logbook')));
    }
}
