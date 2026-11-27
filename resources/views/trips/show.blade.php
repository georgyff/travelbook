@use('App\Services\Figures')

<x-site-page :title="$trip->name.' — Trips'" current="trips" main-class="trip">
    <p class="trip__back"><a href="{{ route('trips.index') }}">← All trips</a></p>

    <header class="trip-head">
        <div class="sc muted trip-head__kicker">{{ Str::lower($trip->status->label()) }}@if ($trip->type) · {{ Str::lower($trip->type->label()) }}@endif</div>
        @if ($trip->countries->whereNotNull('flag_code')->isNotEmpty())
            <div class="flag flag--large trip-head__flags" aria-hidden="true">{{ $trip->countries->whereNotNull('flag_code')->map->flagEmoji()->join('') }}</div>
        @endif
        <h1 id="trip-name" tabindex="-1">{{ $trip->name }}</h1>
        @php($daysText = $trip->days !== null ? $trip->days.' '.Str::plural('day', $trip->days) : null)
        @if ($trip->dateRange() || $daysText)
            <p class="trip-head__desc">{{ collect([$trip->dateRange(), $daysText])->filter()->join(' · ') }}</p>
        @endif
        <div class="trip-head__edit" data-edit-only>
            <button type="button" class="btn-text" data-open-form="{{ route('trips.edit', $trip) }}">edit trip</button>
        </div>
    </header>

    <div class="trip-cols">
        <section aria-labelledby="h-journey">
            <h2 class="trip-section-title" id="h-journey">Journey</h2>
            <dl class="ledger">
                <div class="ledger__row"><dt>Dates</dt><dd><x-figure :value="$trip->dateRange()" /></dd></div>
                <div class="ledger__row ledger__row--top">
                    <dt>Countries</dt>
                    <dd>
                        @forelse ($trip->countries as $tripCountry)
                            <a class="trip-country" href="{{ route('countries.show', $tripCountry) }}">@if ($tripCountry->flag_code)<span class="flag" aria-hidden="true">{{ $tripCountry->flagEmoji() }}</span> @endif{{ $tripCountry->name }}</a>@if (! $loop->last)<span class="sr-only">,</span>@endif
                        @empty
                            <x-figure :value="null" />
                        @endforelse
                    </dd>
                </div>
                <div class="ledger__row ledger__row--top">
                    <dt>Places visited</dt>
                    <dd>
                        @forelse ($trip->placesVisitedWithLinks() as $visited)
                            @if ($visited['url'])
                                <a class="trip-country" href="{{ $visited['url'] }}">{{ $visited['name'] }}</a>@if (! $loop->last)<span class="sr-only">,</span>@endif
                            @else
                                <span class="trip-country">{{ $visited['name'] }}</span>@if (! $loop->last)<span class="sr-only">,</span>@endif
                            @endif
                        @empty
                            <x-figure :value="null" />
                        @endforelse
                    </dd>
                </div>
                <div class="ledger__row"><dt>Continent</dt><dd><x-figure :value="$trip->regions()->pluck('name')->join(', ')" /></dd></div>
                <div class="ledger__row"><dt>Days</dt><dd class="oldstyle"><x-figure :value="Figures::number($trip->days)" /></dd></div>
                <div class="ledger__row"><dt>Type</dt><dd><x-figure :value="$trip->type?->label()" /></dd></div>
                <div class="ledger__row"><dt>Walked steps</dt><dd class="oldstyle"><x-figure :value="Figures::number($trip->walked_steps)" /></dd></div>
                <div class="ledger__row"><dt>Walked km</dt><dd class="oldstyle"><x-figure :value="Figures::number($trip->walked_km, 1)" /></dd></div>
                <div class="ledger__row"><dt>Pictures</dt><dd class="oldstyle"><x-figure :value="Figures::number($trip->pictures)" /></dd></div>
            </dl>
        </section>

        <section aria-labelledby="h-cost">
            <h2 class="trip-section-title" id="h-cost">Cost</h2>
            <dl class="ledger">
                <div class="ledger__row"><dt>Inter-country / city travel</dt><dd class="oldstyle"><x-figure :value="Figures::euros($trip->cost_travel)" /></dd></div>
                <div class="ledger__row"><dt>Housing</dt><dd class="oldstyle"><x-figure :value="Figures::euros($trip->cost_housing)" /></dd></div>
                <div class="ledger__row"><dt>In-travel</dt><dd class="oldstyle"><x-figure :value="Figures::euros($trip->cost_in_travel)" /></dd></div>
                <div class="ledger__row ledger__row--total"><dt>Total</dt><dd class="oldstyle"><x-figure :value="Figures::euros($trip->totalCost())" /></dd></div>
            </dl>

            <div class="per-day">
                <div class="sc muted">avg eur per day</div>
                @if ($trip->averagePerDay() !== null)
                    <div class="per-day__n">{{ Figures::euros(round($trip->averagePerDay(), 2)) }}</div>
                    <div class="italic muted">in-travel {{ Figures::euros($trip->cost_in_travel) }} ÷ {{ $trip->days }} {{ Str::plural('day', $trip->days) }}</div>
                @else
                    <div class="per-day__n muted" aria-hidden="true">—</div>
                    <div class="italic muted">Fill in In-travel and Days to work this out.</div>
                @endif
            </div>
        </section>
    </div>

    <section class="trip-itinerary" aria-labelledby="h-itinerary">
        <div class="history-head">
            <h2 class="trip-section-title" id="h-itinerary">Itinerary</h2>
            <button type="button" class="btn-text" data-edit-only data-open-form="{{ route('trips.itinerary.edit', $trip) }}">edit<span class="sr-only"> itinerary</span></button>
        </div>

        @if ($trip->stops->isEmpty())
            <p class="empty">No itinerary yet.</p>
        @else
            <div class="table-scroll" role="region" aria-labelledby="h-itinerary" tabindex="0">
                <table class="trips-table trips-table--plain">
                    <thead>
                        <tr>
                            <th scope="col">Date</th>
                            <th scope="col">City</th>
                            <th scope="col" class="num">Transport cost</th>
                            <th scope="col" class="num">Hotel cost</th>
                            <th scope="col">Dep. &amp; arr. times</th>
                            <th scope="col">Country</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($trip->stops as $stop)
                            <tr>
                                <td class="nowrap"><x-figure :value="$stop->visited_on?->format('j M Y')" /></td>
                                <td class="trips-table__name"><x-figure :value="$stop->city" /></td>
                                <td class="num"><x-figure :value="Figures::euros($stop->transport_cost)" /></td>
                                <td class="num"><x-figure :value="Figures::euros($stop->hotel_cost)" /></td>
                                <td><x-figure :value="$stop->times" /></td>
                                <td><x-figure :value="$stop->country" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th scope="row" colspan="2">Total</th>
                            <td class="num">{{ Figures::euros((float) $trip->stops->sum('transport_cost')) }}</td>
                            <td class="num">{{ Figures::euros((float) $trip->stops->sum('hotel_cost')) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </section>
</x-site-page>
