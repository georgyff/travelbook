@php($placeCount = (int) ($country->places_count ?? 0))

<x-editor-form
    :title="$country->exists ? 'Edit '.$country->name : 'Add a country'"
    :action="$country->exists ? route('countries.update', $country) : route('countries.store')"
    :method="$country->exists ? 'PUT' : 'POST'"
    :submit-label="$country->exists ? 'Save' : 'Add country'"
    :delete-action="$country->exists ? route('countries.destroy', $country) : null"
    delete-label="Delete country"
    :delete-confirm="'Delete '.$country->name.($placeCount > 0 ? ' and its '.$placeCount.' '.Str::plural('place', $placeCount) : '').'? This can’t be undone.'"
>
    <x-field label="Name" for="country-name-input" name="name">
        <input class="input" id="country-name-input" name="name" value="{{ $country->name }}" maxlength="100" autocomplete="off" required data-autofocus>
    </x-field>

    <x-field label="Region" for="country-region" name="region_id">
        <select class="input" id="country-region" name="region_id" required>
            @unless ($country->region_id)
                <option value="">Choose a region</option>
            @endunless
            @foreach ($regions as $region)
                <option value="{{ $region->id }}" @selected((int) $country->region_id === $region->id)>{{ $region->name }}</option>
            @endforeach
        </select>
    </x-field>

    <x-status-field :selected="$country->status" id-prefix="country-status" />

    <x-field label="History" for="country-history" name="history" hint="Line breaks are kept. Write **bold** or *italic* to format words.">
        <textarea class="input input--history" id="country-history" name="history" rows="8" aria-describedby="country-history-hint">{{ $country->history }}</textarea>
    </x-field>
</x-editor-form>
