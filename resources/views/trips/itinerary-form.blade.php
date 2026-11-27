<x-editor-form
    :title="'Itinerary of '.$trip->name"
    :action="route('trips.itinerary.update', $trip)"
    method="PUT"
>
    <fieldset class="field list-editor grid-editor" data-list-editor data-item-label="Row">
        <legend class="field__label">Itinerary</legend>
        <p class="field__hint">Type each cell in. Rows left completely empty are dropped when you save. Press Enter in a cell to add a row below it.</p>

        <div class="grid-editor__head" aria-hidden="true">
            <span>Date</span><span>City</span><span>Transport cost (€)</span><span>Hotel cost (€)</span><span>Dep. &amp; arr. times</span><span>Country</span><span></span>
        </div>
        <ol class="list-editor__items grid-editor__rows" data-list-items>
            @foreach ($trip->stops as $stop)
                @include('trips.partials.itinerary-row', ['stop' => $stop])
            @endforeach
        </ol>

        <template data-list-template>
            @include('trips.partials.itinerary-row', ['stop' => null])
        </template>

        <p class="field-error" id="stops-error" data-error-for="{{ implode(' ', array_keys(\App\Http\Requests\TripItineraryRequest::COLUMNS)) }}" hidden></p>
        <button type="button" class="btn-outline list-editor__add" data-list-add>Add a row</button>
    </fieldset>
</x-editor-form>
