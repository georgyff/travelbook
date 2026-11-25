<nav class="col-index" aria-labelledby="h-index">
    <div class="block-head">
        <h2 class="index-title" id="h-index">Index of countries</h2>
        <span class="text-buttons" data-edit-only>
            @if ($regions->isNotEmpty())
                <button type="button" class="btn-text" data-open-form="{{ route('countries.create') }}">add country</button>
            @endif
            <button type="button" class="btn-text" data-open-form="{{ route('regions.create') }}">add region</button>
        </span>
    </div>

    <label class="search">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-4-4"></path></svg>
        <span class="sr-only">Filter countries</span>
        <input type="search" placeholder="Look up a country…" autocomplete="off" data-index-filter>
    </label>

    <p class="empty index-empty" data-filter-empty hidden>No country matches.</p>

    @if ($regions->isEmpty())
        <p class="empty index-empty">No regions yet. Choose Edit, then “add region”.</p>
    @endif

    {{-- Same records as the Regions list, so reordering here also reorders there. --}}
    <ul class="region-group-list" id="index-region-groups" data-sort-list data-sort-refresh data-sort-url="{{ route('region-order.update') }}">
        @foreach ($regions as $region)
            @php($countryTotal = $region->countries->count())
            <li class="region-group" data-sort-item data-id="{{ $region->id }}" data-name="{{ $region->name }}" data-region-group="{{ $region->id }}">
                <div class="region-group__head">
                    <x-sort-handle :label="'Reorder region '.$region->name" />
                    <button type="button" class="region-group__toggle" aria-expanded="true" aria-controls="region-{{ $region->id }}-countries" data-region-toggle>
                        <span class="region-group__name sc">{{ Str::lower($region->name) }}</span>
                        <span class="italic muted region-group__count" data-country-count data-total="{{ $countryTotal }}">{{ $countryTotal }} {{ Str::plural('country', $countryTotal) }}</span>
                    </button>
                    <button type="button" class="btn-text" data-edit-only data-open-form="{{ route('regions.edit', $region) }}">edit<span class="sr-only"> region {{ $region->name }}</span></button>
                </div>

                <ul class="country-list" id="region-{{ $region->id }}-countries" data-sort-list data-sort-url="{{ route('regions.country-order.update', $region) }}">
                    @foreach ($region->countries as $listedCountry)
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
                        <button type="button" class="btn-text" data-open-form="{{ route('countries.create', ['region' => $region->id]) }}">add a country to {{ $region->name }}</button>
                    </li>
                </ul>
            </li>
        @endforeach
    </ul>
</nav>
