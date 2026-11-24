{{-- The shell of every edit dialog form. Submitted with fetch by resources/js/logbook.js. --}}
@props([
    'title',
    'action',
    'method' => 'POST',
    'submitLabel' => 'Save',
    'deleteAction' => null,
    'deleteLabel' => 'Delete',
    'deleteConfirm' => null,
    'deleteBlockedReason' => null,
])

<form method="POST" action="{{ $action }}" data-editor-form novalidate>
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <header class="editor__head">
        <h2 id="editor-title">{{ $title }}</h2>
        <button type="button" class="btn-text" data-editor-close>Close</button>
    </header>

    <div class="editor__body">
        <p class="field-error" id="editor-form-error" data-error-for="_form" role="alert" hidden></p>
        {{ $slot }}
    </div>

    <footer class="editor__foot">
        @if ($deleteAction)
            <button type="button" class="btn-outline" data-delete-action="{{ $deleteAction }}" data-delete-confirm="{{ $deleteConfirm }}">{{ $deleteLabel }}</button>
        @elseif ($deleteBlockedReason)
            <p class="field__hint">{{ $deleteBlockedReason }}</p>
        @endif
        <div class="editor__foot-end">
            <button type="button" class="btn-outline" data-editor-close>Cancel</button>
            <button type="submit" class="btn-solid">{{ $submitLabel }}</button>
        </div>
    </footer>
</form>
