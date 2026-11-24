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
        <select class="input" id="place-type" name="place_type_id" required>
            @unless ($place->place_type_id)
                <option value="">Choose a type</option>
            @endunless
            @foreach ($placeTypes as $placeType)
                <option value="{{ $placeType->id }}" @selected((int) $place->place_type_id === $placeType->id)>{{ $placeType->name }}</option>
            @endforeach
        </select>
    </x-field>

    <div class="field">
        <label class="check" for="place-capital">
            <input type="checkbox" id="place-capital" name="is_capital" value="1" @checked($place->is_capital) @if ($currentCapital) aria-describedby="place-capital-hint" @endif>
            This is the capital
        </label>
        @if ($currentCapital)
            <p class="field__hint" id="place-capital-hint">A country has one capital. Ticking this replaces {{ $currentCapital->name }}.</p>
        @endif
    </div>

    <x-status-field :selected="$place->status" id-prefix="place-status" />

    <x-field label="Short history" for="place-history" name="short_history" hint="Line breaks are kept.">
        <textarea class="input" id="place-history" name="short_history" rows="5" aria-describedby="place-history-hint">{{ $place->short_history }}</textarea>
    </x-field>

    <x-list-editor
        name="facts"
        legend="Interesting facts"
        item-label="Fact"
        add-label="Add a fact"
        :items="$place->facts ?? []"
        :multiline="true"
        :reorderable="true"
    />
</x-editor-form>
