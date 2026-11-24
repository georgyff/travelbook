<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The country's history text, edited on its own from the History section.
 */
class CountryHistoryController extends Controller
{
    public function edit(Country $country): View
    {
        return view('logbook.forms.history', ['country' => $country]);
    }

    public function update(Request $request, Country $country): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'history' => ['nullable', 'string', 'max:20000'],
        ]);

        $country->update(['history' => $validated['history'] ?? null]);

        return $this->finishedAt(url()->previous(route('countries.show', $country)));
    }
}
