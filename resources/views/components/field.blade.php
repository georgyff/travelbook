{{-- A labelled form control with an optional hint and a slot for server validation errors. --}}
@props(['label', 'for', 'name', 'hint' => null])

<div class="field">
    <label class="field__label" for="{{ $for }}">{{ $label }}</label>
    @if ($hint)
        <p class="field__hint" id="{{ $for }}-hint">{{ $hint }}</p>
    @endif
    {{ $slot }}
    <p class="field-error" id="{{ $for }}-error" data-error-for="{{ $name }}" hidden></p>
</div>
