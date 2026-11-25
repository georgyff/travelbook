<article class="col-country" @if ($country) aria-labelledby="country-name" @else aria-label="Country" @endif>
    @if (! $country)
        <p class="empty country-empty">
            {{ $regions->isEmpty() ? 'Add a region, then a country, to begin.' : 'Add a country to begin.' }}
            Choose Edit at the top of the page.
        </p>
    @else
        <header class="country-head">
            <div class="sc muted country-head__region">{{ Str::lower($country->region->name) }}</div>
            <h1 id="country-name" tabindex="-1">{{ $country->name }}</h1>
            <p class="country-head__desc">{{ $descriptor }}</p>
            <div class="country-head__meta">
                <span class="status-chip chip--{{ $country->status->chipModifier() }}">{{ ucfirst($country->status->label()) }}</span>
                <span>{{ $placeCounts['completed'] }} completed · {{ $placeCounts['partial'] }} partial · {{ $placeCounts['not_completed'] }} not yet</span>
            </div>
            <div class="country-head__edit" data-edit-only>
                <button type="button" class="btn-text" data-open-form="{{ route('countries.edit', $country) }}">edit country</button>
            </div>
        </header>

        <section aria-labelledby="h-places">
            <div class="section-head">
                <h2 id="h-places">Places</h2>
                <button type="button" class="btn-outline" data-edit-only data-open-form="{{ route('countries.places.create', $country) }}">Add a place</button>
            </div>

            @if (empty($placeGroups))
                <p class="empty places-empty">No places recorded yet.</p>
            @else
                <div class="place-columns">
                    @foreach ($placeGroups as $group)
                        <div class="place-group" role="group" aria-labelledby="group-{{ $group['key'] }}">
                            <h3 id="group-{{ $group['key'] }}">{{ $group['heading'] }}</h3>
                            @foreach ($group['places'] as $place)
                                <button type="button" class="place-btn" data-place-toggle="{{ $place->id }}" aria-controls="place-{{ $place->id }}" aria-pressed="{{ $selectedPlace?->is($place) ? 'true' : 'false' }}">
                                    <span class="marker" data-status="{{ $place->status->value }}" aria-hidden="true"></span>{{ $place->name }}<span class="sr-only">, {{ $place->status->label() }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                <div class="legend">
                    <span><span class="marker" data-status="completed"></span>completed</span>
                    <span><span class="marker" data-status="partial"></span>partially completed</span>
                    <span><span class="marker" data-status="not_completed"></span>not completed</span>
                </div>
            @endif
        </section>

        @foreach ($country->places as $place)
            <section class="place-detail" id="place-{{ $place->id }}" aria-labelledby="place-{{ $place->id }}-title" data-place-detail="{{ $place->id }}" @unless ($selectedPlace?->is($place)) hidden @endunless>
                <div class="place-detail__head">
                    <h2 id="place-{{ $place->id }}-title">{{ $place->name }} <small>{{ $place->is_capital ? 'capital' : Str::lower($place->placeType->name) }}, {{ $place->status->label() }}</small></h2>
                    <button type="button" class="btn-outline" data-edit-only data-open-form="{{ route('places.edit', $place) }}">Edit place</button>
                </div>
                <div class="place-detail__cols">
                    <div>
                        <h3 class="sc">short history</h3>
                        @if (filled($place->short_history))
                            <p>@inlineMarkdown($place->short_history)</p>
                        @else
                            <p class="empty">Nothing written yet.</p>
                        @endif
                    </div>
                    <div>
                        <h3 class="sc">interesting facts</h3>
                        @if (! empty($place->facts))
                            <ol>
                                @foreach ($place->facts as $fact)
                                    <li>@inlineMarkdown($fact)</li>
                                @endforeach
                            </ol>
                        @else
                            <p class="empty">No facts yet.</p>
                        @endif
                    </div>
                </div>
            </section>
        @endforeach

        <section aria-labelledby="h-history">
            <div class="history-head">
                <h2 id="h-history">History</h2>
                <button type="button" class="btn-text" data-edit-only data-open-form="{{ route('countries.history.edit', $country) }}">edit<span class="sr-only"> history</span></button>
            </div>
            @if (filled($country->history))
                <p class="history">@inlineMarkdown($country->history)</p>
            @else
                <p class="history empty">No history written yet.</p>
            @endif
        </section>
    @endif
</article>
