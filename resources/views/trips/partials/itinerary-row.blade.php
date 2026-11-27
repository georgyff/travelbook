{{-- One editable row of the itinerary. Every cell is typed in by hand. --}}

<li class="grid-editor__row" data-list-item>
    <label class="grid-editor__cell">
        <span class="grid-editor__cell-label">Date</span>
        <input class="input" type="date" name="stop_dates[]" value="{{ $stop?->visited_on?->format('Y-m-d') }}" data-list-input data-cell-label="Date">
    </label>
    <label class="grid-editor__cell">
        <span class="grid-editor__cell-label">City</span>
        <input class="input" type="text" name="stop_cities[]" value="{{ $stop?->city }}" maxlength="150" autocomplete="off" data-list-input data-cell-label="City">
    </label>
    <label class="grid-editor__cell">
        <span class="grid-editor__cell-label">Transport cost (€)</span>
        <input class="input" type="number" name="stop_transport[]" value="{{ $stop?->transport_cost }}" min="0" step="0.01" inputmode="decimal" data-list-input data-cell-label="Transport cost">
    </label>
    <label class="grid-editor__cell">
        <span class="grid-editor__cell-label">Hotel cost (€)</span>
        <input class="input" type="number" name="stop_hotel[]" value="{{ $stop?->hotel_cost }}" min="0" step="0.01" inputmode="decimal" data-list-input data-cell-label="Hotel cost">
    </label>
    <label class="grid-editor__cell">
        <span class="grid-editor__cell-label">Dep. &amp; arr. times</span>
        <input class="input" type="text" name="stop_times[]" value="{{ $stop?->times }}" maxlength="100" autocomplete="off" placeholder="08:15 → 11:40" data-list-input data-cell-label="Departure and arrival times">
    </label>
    <label class="grid-editor__cell">
        <span class="grid-editor__cell-label">Country</span>
        <input class="input" type="text" name="stop_countries[]" value="{{ $stop?->country }}" maxlength="100" autocomplete="off" data-list-input data-cell-label="Country">
    </label>
    <div class="grid-editor__actions">
        <button type="button" class="icon-btn" data-list-up>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 15l6-6 6 6"></path></svg>
        </button>
        <button type="button" class="icon-btn" data-list-down>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 9l6 6 6-6"></path></svg>
        </button>
        <button type="button" class="icon-btn" data-list-remove>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"></path></svg>
        </button>
    </div>
</li>
