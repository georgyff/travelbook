{{-- One choice from a few, as a row of radio tiles (styled like the status choice). --}}
@props(['legend', 'name', 'options', 'selected' => null, 'idPrefix', 'required' => false])

<fieldset class="field">
    <legend class="field__label">{{ $legend }}</legend>
    <div class="status-options">
        @foreach ($options as $value => $label)
            <label class="status-option" for="{{ $idPrefix }}-{{ $value === '' ? 'none' : $value }}">
                <input type="radio" id="{{ $idPrefix }}-{{ $value === '' ? 'none' : $value }}" name="{{ $name }}" value="{{ $value }}" @checked((string) $selected === (string) $value) @required($required)>
                {{ $label }}
            </label>
        @endforeach
    </div>
    <p class="field-error" id="{{ $idPrefix }}-error" data-error-for="{{ $name }}" hidden></p>
</fieldset>
