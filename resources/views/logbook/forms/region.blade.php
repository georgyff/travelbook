@php($countryCount = (int) ($region->countries_count ?? 0))

<x-editor-form
    :title="$region->exists ? 'Edit '.$region->name : 'Add a region'"
    :action="$region->exists ? route('regions.update', $region) : route('regions.store')"
    :method="$region->exists ? 'PUT' : 'POST'"
    :submit-label="$region->exists ? 'Save' : 'Add region'"
    :delete-action="$region->exists && $countryCount === 0 ? route('regions.destroy', $region) : null"
    delete-label="Delete region"
    :delete-confirm="'Delete the region '.$region->name.'? This can’t be undone.'"
    :delete-blocked-reason="$countryCount > 0 ? 'To delete '.$region->name.', first move or delete its '.$countryCount.' '.Str::plural('country', $countryCount).'.' : null"
>
    <x-field label="Name" for="region-name" name="name" :hint="$region->exists ? null : 'The region appears both in the Regions list and as a group in the Index of countries.'">
        <input class="input" id="region-name" name="name" value="{{ $region->name }}" maxlength="100" autocomplete="off" required data-autofocus @unless ($region->exists) aria-describedby="region-name-hint" @endunless>
    </x-field>
</x-editor-form>
