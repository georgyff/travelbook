@use('App\Enums\TripColumn')
@use('App\Services\Figures')

<x-site-page title="Trips" current="trips" main-class="trips">
    <header class="trips-head">
        <div>
            <h1 id="trips-title">Trips</h1>
            <p class="trips-head__desc">{{ $totals['trips'] }} {{ Str::plural('trip', $totals['trips']) }} · {{ $totals['countries'] }} {{ Str::plural('country', $totals['countries']) }} · {{ Figures::number($totals['days']) }} {{ Str::plural('day', $totals['days']) }}</p>
        </div>
        <button type="button" class="btn-outline" data-edit-only data-open-form="{{ route('trips.create') }}">Add a trip</button>
    </header>

    @if ($trips->isEmpty())
        <p class="empty">No trips yet. Choose Edit, then “Add a trip”.</p>
    @else
        <div class="table-scroll" role="region" aria-labelledby="trips-title" tabindex="0">
            <table class="trips-table">
                <caption class="sr-only">Trips, sorted by {{ Str::lower($sortColumn->label()) }}, {{ $descending ? 'descending' : 'ascending' }}. Choose a column heading to sort by it.</caption>
                <thead>
                    <tr>
                        @foreach (TripColumn::cases() as $column)
                            @php($isSorted = $column === $sortColumn)
                            <th scope="col" @class(['num' => $column->isNumeric(), 'is-sorted' => $isSorted]) @if ($isSorted) aria-sort="{{ $descending ? 'descending' : 'ascending' }}" @endif>
                                <a class="sort-link" href="{{ route('trips.index', ['sort' => $column->value, 'direction' => $isSorted && ! $descending ? 'desc' : 'asc']) }}">
                                    {{ $column->label() }}<span class="sort-link__arrow" aria-hidden="true">{{ $isSorted ? ($descending ? '↓' : '↑') : '' }}</span>
                                </a>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($trips as $trip)
                        <tr>
                            <th scope="row" class="trips-table__name"><a href="{{ route('trips.show', $trip) }}">{{ $trip->name }}</a></th>
                            <td class="nowrap">{{ $trip->status->label() }}</td>
                            <td class="nowrap"><x-figure :value="$trip->dateRange()" /></td>
                            <td>
                                @forelse ($trip->countries as $tripCountry)
                                    <span class="trip-country">@if ($tripCountry->flag_code)<span class="flag" aria-hidden="true">{{ $tripCountry->flagEmoji() }}</span> @endif{{ $tripCountry->name }}</span>@if (! $loop->last)<span class="sr-only">,</span>@endif
                                @empty
                                    <x-figure :value="null" />
                                @endforelse
                            </td>
                            <td><x-figure :value="implode(', ', $trip->places_visited ?? [])" /></td>
                            <td>
                                <x-figure :value="$trip->regions()->pluck('name')->join(', ')" />
                            </td>
                            <td class="num"><x-figure :value="Figures::number($trip->days)" /></td>
                            <td class="num"><x-figure :value="Figures::number($trip->walked_steps)" /></td>
                            <td class="num"><x-figure :value="Figures::number($trip->walked_km, 1)" /></td>
                            <td class="num"><x-figure :value="Figures::euros($trip->totalCost())" /></td>
                            <td class="nowrap"><x-figure :value="$trip->type?->label()" /></td>
                            <td class="num"><x-figure :value="Figures::number($trip->pictures)" /></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="row">Total</th>
                        <td colspan="2"></td>
                        <td>{{ $totals['countries'] }} {{ Str::plural('country', $totals['countries']) }}</td>
                        <td>{{ $totals['places'] }} {{ Str::plural('place', $totals['places']) }}</td>
                        <td></td>
                        <td class="num">{{ Figures::number($totals['days']) }}</td>
                        <td class="num">{{ Figures::number($totals['steps']) }}</td>
                        <td class="num">{{ Figures::number($totals['km'], 1) }}</td>
                        <td class="num">{{ Figures::euros($totals['cost']) }}</td>
                        <td></td>
                        <td class="num">{{ Figures::number($totals['pictures']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</x-site-page>
