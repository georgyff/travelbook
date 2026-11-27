<x-site-page
    :title="($country ? $country->name.' — ' : '').'Logbook'"
    current="logbook"
    main-class="layout"
    edit-notice="Editing. Use the add and edit buttons to change anything. To reorder, drag a dotted handle, or focus it and press the up and down arrow keys."
>
    @include('logbook.partials.progress')
    @include('logbook.partials.index')
    @include('logbook.partials.country')
</x-site-page>
