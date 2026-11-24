<x-editor-form
    :title="'History of '.$country->name"
    :action="route('countries.history.update', $country)"
    method="PUT"
>
    <x-field label="History" for="history-text" name="history" hint="Line breaks are kept.">
        <textarea class="input input--history" id="history-text" name="history" rows="12" aria-describedby="history-text-hint" data-autofocus>{{ $country->history }}</textarea>
    </x-field>
</x-editor-form>
