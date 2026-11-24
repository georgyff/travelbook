{{-- Completed / partially completed / not completed, as radio buttons with their status markers. --}}
@props(['selected', 'idPrefix'])

@php($selectedStatus = $selected instanceof \App\Enums\CompletionStatus ? $selected : \App\Enums\CompletionStatus::tryFrom((string) $selected))

<fieldset class="field">
    <legend class="field__label">Status</legend>
    <div class="status-options">
        @foreach (\App\Enums\CompletionStatus::cases() as $status)
            <label class="status-option" for="{{ $idPrefix }}-{{ $status->value }}">
                <input type="radio" id="{{ $idPrefix }}-{{ $status->value }}" name="status" value="{{ $status->value }}" @checked($selectedStatus === $status) required>
                <span class="marker" data-status="{{ $status->value }}" aria-hidden="true"></span>
                {{ ucfirst($status->label()) }}
            </label>
        @endforeach
    </div>
    <p class="field-error" id="{{ $idPrefix }}-status-error" data-error-for="status" hidden></p>
</fieldset>
