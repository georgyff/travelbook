@use('App\Enums\TripStatus')
@use('App\Enums\TripType')

<x-editor-form
    :title="$trip->exists ? 'Edit '.$trip->name : 'Add a trip'"
    :action="$trip->exists ? route('trips.update', $trip) : route('trips.store')"
    :method="$trip->exists ? 'PUT' : 'POST'"
    :submit-label="$trip->exists ? 'Save' : 'Add trip'"
    :delete-action="$trip->exists ? route('trips.destroy', $trip) : null"
    delete-label="Delete trip"
    :delete-confirm="'Delete '.$trip->name.'? This can’t be undone.'"
>
    <x-field label="Trip" for="trip-name" name="name">
        <input class="input" id="trip-name" name="name" value="{{ $trip->name }}" maxlength="150" autocomplete="off" required data-autofocus>
    </x-field>

    <x-choice-field
        legend="Status"
        name="status"
        id-prefix="trip-status"
        :options="collect(TripStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->all()"
        :selected="$trip->status?->value"
        required
    />

    <div class="field-row">
        <x-field label="Start date" for="trip-starts-on" name="starts_on">
            <input class="input" type="date" id="trip-starts-on" name="starts_on" value="{{ $trip->starts_on?->format('Y-m-d') }}">
        </x-field>
        <x-field label="End date" for="trip-ends-on" name="ends_on">
            <input class="input" type="date" id="trip-ends-on" name="ends_on" value="{{ $trip->ends_on?->format('Y-m-d') }}">
        </x-field>
        <x-field label="Days (by hand)" for="trip-days" name="days">
            <input class="input" type="number" id="trip-days" name="days" value="{{ $trip->days }}" min="0" step="1" inputmode="numeric">
        </x-field>
    </div>

    <datalist id="trip-country-options">
        @foreach ($countryNames as $countryName)
            <option value="{{ $countryName }}"></option>
        @endforeach
    </datalist>
    <x-list-editor
        name="countries"
        legend="Countries"
        item-label="Country"
        add-label="Add a country"
        hint="Type to pick from the Logbook’s countries, in the order you travelled. The trip’s continent comes from their regions."
        :items="$trip->countries->pluck('name')->all()"
        :reorderable="true"
        datalist="trip-country-options"
    />

    <datalist id="trip-place-options">
        @foreach ($placeNames as $placeName)
            <option value="{{ $placeName }}"></option>
        @endforeach
    </datalist>
    <x-list-editor
        name="places_visited"
        legend="Places visited"
        item-label="Place"
        add-label="Add a place"
        hint="Any place you went, e.g. Pisa, Florence, Lucca. Suggestions come from the Logbook; a match there links to it."
        :items="$trip->places_visited ?? []"
        :reorderable="true"
        datalist="trip-place-options"
    />

    <x-choice-field
        legend="Type"
        name="type"
        id-prefix="trip-type"
        :options="['' => 'Not set'] + collect(TripType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()])->all()"
        :selected="$trip->type?->value ?? ''"
    />

    <div class="field-row">
        <x-field label="Walked steps" for="trip-steps" name="walked_steps">
            <input class="input" type="number" id="trip-steps" name="walked_steps" value="{{ $trip->walked_steps }}" min="0" step="1" inputmode="numeric">
        </x-field>
        <x-field label="Walked km" for="trip-km" name="walked_km">
            <input class="input" type="number" id="trip-km" name="walked_km" value="{{ $trip->walked_km }}" min="0" step="0.1" inputmode="decimal">
        </x-field>
        <x-field label="Pictures" for="trip-pictures" name="pictures">
            <input class="input" type="number" id="trip-pictures" name="pictures" value="{{ $trip->pictures }}" min="0" step="1" inputmode="numeric">
        </x-field>
    </div>

    <fieldset class="field">
        <legend class="field__label">Cost in EUR</legend>
        <p class="field__hint">The total is the sum of these three. AVG EUR per day is In-travel ÷ Days.</p>
        <div class="field-row">
            <x-field label="Inter-country / city travel" for="trip-cost-travel" name="cost_travel">
                <input class="input" type="number" id="trip-cost-travel" name="cost_travel" value="{{ $trip->cost_travel }}" min="0" step="0.01" inputmode="decimal">
            </x-field>
            <x-field label="Housing" for="trip-cost-housing" name="cost_housing">
                <input class="input" type="number" id="trip-cost-housing" name="cost_housing" value="{{ $trip->cost_housing }}" min="0" step="0.01" inputmode="decimal">
            </x-field>
            <x-field label="In-travel" for="trip-cost-in-travel" name="cost_in_travel">
                <input class="input" type="number" id="trip-cost-in-travel" name="cost_in_travel" value="{{ $trip->cost_in_travel }}" min="0" step="0.01" inputmode="decimal">
            </x-field>
        </div>
    </fieldset>
</x-editor-form>
