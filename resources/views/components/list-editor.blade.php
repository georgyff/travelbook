{{--
    An editable list of text values submitted as name[] (facts, region countries).
    resources/js/logbook.js adds, removes and reorders rows and keeps their labels numbered.
--}}
@props([
    'name',
    'legend',
    'itemLabel',
    'addLabel',
    'items' => [],
    'hint' => null,
    'multiline' => false,
    'reorderable' => false,
    'datalist' => null,
])

<fieldset class="field list-editor" data-list-editor data-item-label="{{ $itemLabel }}">
    <legend class="field__label">{{ $legend }}</legend>
    @if ($hint)
        <p class="field__hint">{{ $hint }}</p>
    @endif

    <ol class="list-editor__items" data-list-items>
        @foreach ($items as $value)
            <x-list-editor.item :name="$name" :value="$value" :multiline="$multiline" :reorderable="$reorderable" :datalist="$datalist" />
        @endforeach
    </ol>

    <template data-list-template>
        <x-list-editor.item :name="$name" value="" :multiline="$multiline" :reorderable="$reorderable" :datalist="$datalist" />
    </template>

    <p class="field-error" id="{{ $name }}-error" data-error-for="{{ $name }}" hidden></p>
    <button type="button" class="btn-outline list-editor__add" data-list-add>{{ $addLabel }}</button>
</fieldset>
