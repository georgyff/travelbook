<x-editor-form
    :title="$place->exists ? 'Edit '.$place->name : 'Add a place in '.$country->name"
    :action="$place->exists ? route('places.update', $place) : route('countries.places.store', $country)"
    :method="$place->exists ? 'PUT' : 'POST'"
    :submit-label="$place->exists ? 'Save' : 'Add place'"
    :delete-action="$place->exists ? route('places.destroy', $place) : null"
    delete-label="Delete place"
    :delete-confirm="'Delete '.$place->name.'? This can’t be undone.'"
>
    <x-field label="Name" for="place-name" name="name">
        <input class="input" id="place-name" name="name" value="{{ $place->name }}" maxlength="150" autocomplete="off" required data-autofocus>
    </x-field>

    <x-field label="Type" for="place-type" name="place_type_id">
        <span class="select">
            <select class="input" id="place-type" name="place_type_id" required>
                @unless ($place->place_type_id)
                    <option value="">Choose a type</option>
                @endunless
                @foreach ($placeTypes as $placeType)
                    <option value="{{ $placeType->id }}" @selected((int) $place->place_type_id === $placeType->id)>{{ $placeType->name }}</option>
                @endforeach
            </select>
        </span>
    </x-field>

    <div class="field">
        <label class="check" for="place-capital">
            <input type="checkbox" id="place-capital" name="is_capital" value="1" @checked($place->is_capital) @if ($otherCapitals->isNotEmpty()) aria-describedby="place-capital-hint" @endif>
            This is a capital
        </label>
        @if ($otherCapitals->isNotEmpty())
            <p class="field__hint" id="place-capital-hint">{{ $country->name }} already has {{ $otherCapitals->count() === 1 ? 'a capital' : 'capitals' }}: {{ $otherCapitals->join(', ', ' and ') }}. A country can have more than one.</p>
        @endif
    </div>

    <x-status-field :selected="$place->status" id-prefix="place-status" />

    <div class="field-row">
        <x-field label="Times visited" for="place-times-visited" name="times_visited">
            <input class="input" type="number" id="place-times-visited" name="times_visited" value="{{ $place->times_visited }}" min="0" step="1" inputmode="numeric">
        </x-field>
        <x-field label="Pictures" for="place-pictures" name="pictures">
            <input class="input" type="number" id="place-pictures" name="pictures" value="{{ $place->pictures }}" min="0" step="1" inputmode="numeric">
        </x-field>
    </div>

    <x-field label="Short history" for="place-history" name="short_history" hint="Line breaks are kept. Write **bold** or *italic* to format words.">
        <textarea class="input input--long" id="place-history" name="short_history" rows="10" aria-describedby="place-history-hint">{{ $place->short_history }}</textarea>
    </x-field>

    <x-list-editor
        name="facts"
        legend="Interesting facts"
        item-label="Fact"
        add-label="Add a fact"
        hint="Write **bold** or *italic* to format words."
        :items="$place->facts ?? []"
        :multiline="true"
        :reorderable="true"
    />
</x-editor-form>
