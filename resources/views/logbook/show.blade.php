<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $country ? $country->name.' — ' : '' }}Logbook — The Travel Log</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400&display=swap" rel="stylesheet">
    {{-- Apply edit mode before first paint so the controls don't flash. --}}
    <script>try { if (sessionStorage.getItem('logbook:editing') === '1') { document.documentElement.classList.add('is-editing'); } } catch (e) {}</script>
    @vite(['resources/css/logbook.css', 'resources/js/logbook.js'])
</head>
<body>
    <header class="masthead">
        <div class="masthead__row">
            <div class="wordmark">The Travel Log</div>
            <nav class="mainnav" aria-label="Main">
                <a href="{{ route('logbook') }}" aria-current="page">Logbook</a>
                <span class="mainnav__soon">Trips<span class="sr-only"> (coming soon)</span></span>
                <button type="button" class="btn-text" data-edit-toggle>Edit</button>
            </nav>
        </div>
        <div class="masthead__double"></div>
        <p class="edit-notice" data-edit-only>
            Editing. Use the add and edit buttons to change anything. To reorder, drag a dotted handle, or focus it and press the up and down arrow keys.
        </p>
    </header>

    <main class="layout" id="logbook">
        @include('logbook.partials.progress')
        @include('logbook.partials.index')
        @include('logbook.partials.country')
    </main>

    <dialog class="editor" id="editor" aria-labelledby="editor-title"></dialog>
    <p class="sr-only" id="sort-help">Press the up or down arrow key to move it.</p>
    <div class="sr-only" id="announcer" aria-live="polite"></div>
</body>
</html>
