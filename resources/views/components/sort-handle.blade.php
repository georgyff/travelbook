{{-- Drag handle for reordering (edit mode). Arrow keys move the item when the handle has focus. --}}
@props(['label'])

<button type="button" class="handle" data-edit-only data-sort-handle aria-label="{{ $label }}" aria-describedby="sort-help">
    <svg width="12" height="18" viewBox="0 0 12 18" fill="currentColor" aria-hidden="true"><circle cx="3" cy="3" r="1.5"></circle><circle cx="9" cy="3" r="1.5"></circle><circle cx="3" cy="9" r="1.5"></circle><circle cx="9" cy="9" r="1.5"></circle><circle cx="3" cy="15" r="1.5"></circle><circle cx="9" cy="15" r="1.5"></circle></svg>
</button>
