@php($world = $countryProgress['world'])

<aside class="col-progress" aria-label="Progress">
    <div class="world">
        <div class="sc muted">the world</div>
        <div class="world__n">{{ $world['done'] }}</div>
        <div class="italic muted">of {{ $world['total'] }} {{ Str::plural('country', $world['total']) }} · {{ $world['percent'] }}%</div>
    </div>

    <section aria-labelledby="h-regions">
        <div class="block-head">
            <h2 class="sc" id="h-regions">regions</h2>
            <button type="button" class="btn-text" data-edit-only data-open-form="{{ route('regions.create') }}">add<span class="sr-only"> a region</span></button>
        </div>

        @if (empty($countryProgress['regions']))
            <p class="empty">No regions yet.</p>
        @endif

        <ul class="region-list" data-sort-list data-sort-url="{{ route('region-order.update') }}">
            @foreach ($countryProgress['regions'] as $tally)
                <li class="row" data-sort-item data-id="{{ $tally['region']->id }}" data-name="{{ $tally['region']->name }}">
                    <x-sort-handle :label="'Reorder region '.$tally['region']->name" />
                    <span>{{ $tally['region']->name }}</span>
                    <span class="leader" aria-hidden="true"></span>
                    <span class="oldstyle">{{ $tally['done'] }} <span class="muted italic">of</span> {{ $tally['total'] }}</span>
                    <button type="button" class="btn-text" data-edit-only data-open-form="{{ route('regions.edit', $tally['region']) }}">edit<span class="sr-only"> region {{ $tally['region']->name }}</span></button>
                </li>
            @endforeach
        </ul>
    </section>

    <section aria-labelledby="h-place-types">
        <div class="block-head"><h2 class="sc" id="h-place-types">places by type</h2></div>
        @foreach ($placesByType as $tally)
            <div class="row row--center">
                <span class="row__label">{{ $tally['placeType']->plural_name }}</span>
                <span class="chip chip--completed">{{ $tally['completed'] }}<span class="sr-only"> completed</span></span>
                <span class="muted" aria-hidden="true">+</span>
                <span class="chip chip--partial">{{ $tally['partial'] }}<span class="sr-only"> partially completed</span></span>
                <span class="muted" aria-hidden="true">=</span>
                <span class="row__sum"><span class="sr-only">total </span>{{ $tally['sum'] }}</span>
            </div>
        @endforeach
    </section>
</aside>
