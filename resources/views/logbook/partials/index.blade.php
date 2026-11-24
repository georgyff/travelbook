<nav class="col-index" aria-labelledby="h-index">
    <div class="block-head">
        <h2 class="index-title" id="h-index">Index of countries</h2>
        <span class="text-buttons" data-edit-only>
            @if ($continents->isNotEmpty())
                <button type="button" class="btn-text" data-open-form="{{ route('countries.create') }}">add country</button>
            @endif
            <button type="button" class="btn-text" data-open-form="{{ route('continents.create') }}">add continent</button>
        </span>
    </div>

    <label class="search">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-4-4"></path></svg>
        <span class="sr-only">Filter countries</span>
        <input type="search" placeholder="Look up a country…" autocomplete="off" data-index-filter>
    </label>

    <p class="empty index-empty" data-filter-empty hidden>No country matches.</p>

    @if ($continents->isEmpty())
        <p class="empty index-empty">No continents yet. Choose Edit, then “add continent”.</p>
    @endif

    <ul class="continent-list" data-sort-list data-sort-url="{{ route('continent-order.update') }}">
        @foreach ($continents as $continent)
            @php($countryTotal = $continent->countries->count())
            <li class="continent" data-sort-item data-id="{{ $continent->id }}" data-name="{{ $continent->name }}" data-continent="{{ $continent->id }}">
                <div class="continent__head">
                    <x-sort-handle :label="'Reorder continent '.$continent->name" />
                    <button type="button" class="continent__toggle" aria-expanded="true" aria-controls="continent-{{ $continent->id }}-countries" data-continent-toggle>
                        <span class="continent__name sc">{{ Str::lower($continent->name) }}</span>
                        <span class="italic muted continent__count" data-country-count data-total="{{ $countryTotal }}">{{ $countryTotal }} {{ Str::plural('country', $countryTotal) }}</span>
                    </button>
                    <button type="button" class="btn-text" data-edit-only data-open-form="{{ route('continents.edit', $continent) }}">edit<span class="sr-only"> continent {{ $continent->name }}</span></button>
                </div>

                <ul class="country-list" id="continent-{{ $continent->id }}-countries" data-sort-list data-sort-url="{{ route('continents.country-order.update', $continent) }}">
                    @foreach ($continent->countries as $listedCountry)
                        <li class="country-row" data-sort-item data-id="{{ $listedCountry->id }}" data-name="{{ $listedCountry->name }}" data-country-row>
                            <x-sort-handle :label="'Reorder '.$listedCountry->name" />
                            <a class="country-btn" href="{{ route('countries.show', $listedCountry) }}" data-country-link @if ($country?->is($listedCountry)) aria-current="page" @endif>
                                <span class="marker" data-status="{{ $listedCountry->status->value }}" aria-hidden="true"></span>
                                <span>{{ $listedCountry->name }}</span><span class="sr-only">, {{ $listedCountry->status->label() }}</span>
                                <span class="leader" aria-hidden="true"></span>
                                <span class="oldstyle muted country-btn__count">{{ $listedCountry->places_count }}<span class="sr-only"> {{ Str::plural('place', $listedCountry->places_count) }}</span></span>
                            </a>
                        </li>
                    @endforeach
                    @if ($countryTotal === 0)
                        <li class="empty index-empty">No countries yet.</li>
                    @endif
                    <li data-edit-only>
                        <button type="button" class="btn-text" data-open-form="{{ route('countries.create', ['continent' => $continent->id]) }}">add a country to {{ $continent->name }}</button>
                    </li>
                </ul>
            </li>
        @endforeach
    </ul>
</nav>
