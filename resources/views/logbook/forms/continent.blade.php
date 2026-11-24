@php($countryCount = (int) ($continent->countries_count ?? 0))

<x-editor-form
    :title="$continent->exists ? 'Edit continent' : 'Add a continent'"
    :action="$continent->exists ? route('continents.update', $continent) : route('continents.store')"
    :method="$continent->exists ? 'PUT' : 'POST'"
    :submit-label="$continent->exists ? 'Save' : 'Add continent'"
    :delete-action="$continent->exists && $countryCount === 0 ? route('continents.destroy', $continent) : null"
    delete-label="Delete continent"
    :delete-confirm="'Delete '.$continent->name.'? This can’t be undone.'"
    :delete-blocked-reason="$countryCount > 0 ? 'To delete '.$continent->name.', first move or delete its '.$countryCount.' '.Str::plural('country', $countryCount).'.' : null"
>
    <x-field label="Name" for="continent-name" name="name">
        <input class="input" id="continent-name" name="name" value="{{ $continent->name }}" maxlength="100" autocomplete="off" required data-autofocus>
    </x-field>
</x-editor-form>
