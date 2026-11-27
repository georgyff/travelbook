{{-- A value, or a muted dash (read out as "none") when there isn't one. --}}
@props(['value'])

@if (filled($value))
{{ $value }}
@else
<span class="muted" aria-hidden="true">—</span><span class="sr-only">none</span>
@endif
