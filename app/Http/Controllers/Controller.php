<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

abstract class Controller
{
    /**
     * Finish a write. The logbook's edit dialogs submit with fetch and expect JSON naming the
     * page to show next; a plain form submission is redirected there instead.
     */
    protected function finishedAt(string $url): JsonResponse|RedirectResponse
    {
        if (request()->expectsJson()) {
            return response()->json(['redirect' => $url]);
        }

        return redirect($url);
    }
}
