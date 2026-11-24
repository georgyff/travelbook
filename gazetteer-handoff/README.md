# Travel Log — Logbook page, "Gazetteer" design

Handoff for implementing the **Logbook** page of a personal travel-log website (replacing a Notion page). This covers the chosen layout (three columns: progress · country index · country page) in the chosen visual style ("Gazetteer": warm paper, all-serif, index-like).

## What's in this folder

| Path | What it is |
|---|---|
| `README.md` | This spec. Read first. |
| `reference/logbook-gazetteer.html` | **Working reference page.** Open it in a browser. Plain HTML/CSS/JS, renders everything from the embedded sample data, computes all derived numbers, and handles selecting countries/places, collapsing continents and filtering. Treat it as the visual and behavioural source of truth. It is not production code. |
| `design/tokens.css` | Colours, type sizes, spacing as CSS custom properties. |
| `data/sample-data.json` | The data model, filled with sample entries (placeholder text, not real content). |
| `design/source/StyleGazetteer.dc.html` | The original canvas artboard (design-tool format, needs that tool's runtime). Only for reference. Use `reference/` instead. |

## Core requirement: nothing is hard-coded

The page has **no premade content**. Every region, continent, country, place, history text and fact comes from stored data the owner can **add, edit, reorder and delete in the UI** without touching code. The sample data exists only to show the layout.

## Data model

See `data/sample-data.json`. Summary:

- **placeTypes**: `City`, `Village`, `Nature`, `Monument`. Keep this list as data so more types can be added later.
- **continents**: `{ id, name, order }`. Used to group the country index.
- **regions**: `{ id, name, order, countries: [countryName…] }`. **User-defined.** A region is a custom list of countries. It may name countries that aren't in the logbook yet; those count as not completed.
- **countries**: `{ id, name, continentId, order, status, history }`
  - `status`: `completed` | `partial` | `not_completed`
  - `order`: a custom order within its continent, set by drag-and-drop in the index
  - `history`: free text, the "country history" section
- **places**: `{ id, countryId, name, type, isCapital, status, shortHistory, facts: [string] }`
  - The capital is a normal `City` with `isCapital: true`. It gets its own icon/group in the UI but still counts as a city in the summary.

### Derived values (compute, never store)

- **Region progress** = countries in the region with status `completed` / number of countries in the region. Shown as e.g. `2 of 3`.
- **World** = sum of all regions' completed counts / sum of all regions' totals (as the owner specified: "World as a sum of all other regions").
- **Places by type**, for each type: `completed` count + `partial` count = sum. For example, Cities `101 + 25 = 126`. The first number uses the white chip, the second the yellow chip, and the sum is plain bold text.
- **Country header counts**: completed / partial / not-yet places in that country.

### Open questions to confirm with the owner

1. Is a country's status set by hand (as modelled) or derived from its places?
2. If a country appears in two regions, World counts it twice. That follows the "sum of regions" spec literally. Confirm or de-duplicate.
3. Does the region figure count only `completed` countries, or `completed + partial`? (Modelled as completed only.)

## Layout

Page max-width 1360px, 32px side padding. A masthead sits above the three columns.

**Masthead.** Wordmark "The Travel Log" (34px, 600, small-caps) on the left. Nav on the right: *Logbook* (current: ink, 600, 2px underline), *Trips* (muted), *Edit* (italic text button). Below the row there's a 1px ink rule, then a second 1px rule 3px lower (a double rule).

**Three columns** (`flex-wrap: wrap`; flex-basis 250 / 260 / 560px; the third column takes the remaining width). The columns are divided by 1px `--rule` vertical lines with 28px padding either side; the country column has 36px left padding. Below about 900px the columns stack and the vertical rules become top rules.

1. **Progress column**
   - **World block:** centred, with ink rules above and below. A small-caps label "the world", the completed count at 76px weight 500, then an italic line "of N countries · P%".
   - **Regions:** a heading row (small-caps "regions" plus an italic "add" button), then one row per region: name … dotted leader … `done of total` in old-style figures.
   - **Places by type:** one row per type, `[done chip] + [partial chip] = sum`.
2. **Country index**
   - **Header and search:** the title "Index of countries" (26px, 600) with an "add" button, then a search field with only a bottom rule and an italic placeholder.
   - **Continent groups:** a small-caps continent name with an italic count ("4 countries"). The whole row is a button that collapses or expands the group.
   - **Country rows** (min height 42px): status marker … name … dotted leader … place count. The selected row has a `--paper-selected` background and weight 600. Rows are drag-to-reorder within their continent.
3. **Country page** (for the selected country)
   - **Header (centred):** a small-caps continent, then the country name (84px, 500), then an italic descriptor ("Capital, Phnom Penh. Seven places recorded."). Under that, the status chip and "3 completed · 2 partial · 2 not yet". An ink rule closes the header.
   - **Places:** the heading plus an "Add a place" outlined button. Places are grouped in this order: Capital, Cities, Monuments, Nature, Villages. They flow in CSS columns (`column-width: 240px`, 40px gap, column-rule in `--rule`). Each group has an italic heading, and each place is a full-width row button (44px min): status marker + name. A status legend sits below.
   - **Place detail** (shown when a place is selected): a `--paper-raised` panel with a 1px ink border. It shows the name (36px) with an italic "type, status" after it, an "Edit place" button, and two columns: **Short history** and **Interesting facts** (an ordered list). Clicking the selected place again closes it.
   - **History:** the heading "History" with an italic "edit" button, then the country history text at 19px with line-height 1.6, max 66ch wide, keeping the owner's line breaks.

## Completion status — visual rule

The owner's rule: **white = completed, yellow = partially completed, red = not completed.** On this light page those are fills, never text colours:

| Status | Marker / chip fill | Border | Text on chip |
|---|---|---|---|
| completed | `#FFFFFF` | `#1E1B16` | ink |
| partial | `#E8B931` | `#1E1B16` | ink |
| not_completed | `#A8322A` | `#A8322A` | `#FFFFFF` |

Markers are 11px squares placed before country and place names. Status is never shown by colour alone: there is always visible text or screen-reader-only text giving it (see `.sr-only` in the reference).

## Editing (all in the UI)

The reference page shows the edit controls but doesn't implement them. The production version needs:

- **Regions:** create, rename, delete, pick countries, reorder.
- **Continents:** create, rename, delete, reorder.
- **Countries:** add, edit (name, continent, status, history), delete, and drag to reorder within a continent.
- **Places:** add, edit (name, type, capital flag, status, short history, facts as an editable list), delete.
- The masthead **Edit** button can switch the whole page into an edit mode, or each "add / edit" control can open an inline form or dialog. Pick one pattern and use it everywhere. Forms use the same tokens: square corners, 1px ink borders, 44px controls.

## Accessibility (already in the reference — keep it)

- Real `<button>`s for every clickable row; `aria-expanded` on continent toggles; `aria-current="page"` on the selected country; `aria-pressed` on the selected place.
- Muted text `#5E574B` on paper is 6.7:1; white on red `#A8322A` is 6.9:1.
- Touch targets are at least 44px (country rows 42px plus their spacing; raise them to 44px if needed).

## Out of scope here, for context

There's also a **Trips** page, designed separately in an earlier, darker style. It isn't restyled yet but should take these Gazetteer tokens. Each trip has these properties: Trip (name), Status (Idea / Booked / Partially-Booked / Ongoing / Done), Date (start → end), Countries (list), Continent, Walked_steps, Walked_km, Cost, Days (entered by hand, not calculated from the dates), Type (Solo / Friends / Mixed / Family / Couple), Pictures. Trips can be added, edited, removed and sorted by any property. A trip page has a cost breakdown in three categories (Inter-Country/City Travel, Housing, In-Travel) and a derived **AVG EUR per day = In-Travel ÷ Days**.

## Suggested prompt for Claude Code

> Build the Logbook page described in `README.md`. Match `reference/logbook-gazetteer.html` visually and behaviourally, use `design/tokens.css` for every colour and size, and use the data model in `data/sample-data.json` (seed with it). All content must be editable in the UI: add, edit, delete and reorder regions, continents, countries and places, and edit histories and facts. Derived numbers (region, world and type totals) are computed, never stored. Before choosing a stack and storage, ask me about the open questions in the README.
