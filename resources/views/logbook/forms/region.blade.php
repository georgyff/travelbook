<x-editor-form
    :title="$region->exists ? 'Edit '.$region->name : 'Add a region'"
    :action="$region->exists ? route('regions.update', $region) : route('regions.store')"
    :method="$region->exists ? 'PUT' : 'POST'"
    :submit-label="$region->exists ? 'Save' : 'Add region'"
    :delete-action="$region->exists ? route('regions.destroy', $region) : null"
    delete-label="Delete region"
    :delete-confirm="'Delete the region '.$region->name.'? Its countries stay in the logbook. This can’t be undone.'"
>
    <x-field label="Name" for="region-name" name="name">
        <input class="input" id="region-name" name="name" value="{{ $region->name }}" maxlength="100" autocomplete="off" required data-autofocus>
    </x-field>

    <x-list-editor
        name="countries"
        legend="Countries"
        item-label="Country"
        add-label="Add a country"
        :items="$memberNames"
        hint="Type a name or pick one from your logbook. Countries that aren’t in the logbook yet count as not completed. Press Enter to add another row."
        datalist="region-country-suggestions"
    />

    <datalist id="region-country-suggestions">
        @foreach ($countryNames as $countryName)
            <option value="{{ $countryName }}"></option>
        @endforeach
    </datalist>
</x-editor-form>
