@props(['name', 'value', 'multiline' => false, 'reorderable' => false, 'datalist' => null])

<li class="list-editor__item" data-list-item>
    @if ($multiline)
        <textarea class="input" name="{{ $name }}[]" rows="3" data-list-input>{{ $value }}</textarea>
    @else
        <input class="input" type="text" name="{{ $name }}[]" value="{{ $value }}" autocomplete="off" data-list-input @if ($datalist) list="{{ $datalist }}" @endif>
    @endif
    @if ($reorderable)
        <button type="button" class="icon-btn" data-list-up>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 15l6-6 6 6"></path></svg>
        </button>
        <button type="button" class="icon-btn" data-list-down>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 9l6 6 6-6"></path></svg>
        </button>
    @endif
    <button type="button" class="icon-btn" data-list-remove>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"></path></svg>
    </button>
</li>
