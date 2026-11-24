/**
 * Logbook page behaviour. The server renders every view, and this module enhances it:
 *  - country links swap the page content in place (history-aware), place rows toggle their detail;
 *  - continents collapse, and the index filters as you type;
 *  - the masthead Edit button switches edit mode, and every add/edit control opens its form in one <dialog>;
 *  - drag handles (pointer or arrow keys) reorder regions, continents and countries.
 * Every write goes to the server, which recomputes all derived numbers. Nothing derived is kept here.
 */

const EDIT_MODE_KEY = 'logbook:editing';
const COLLAPSED_KEY = 'logbook:collapsed-continents';
const NARROW_LAYOUT = '(max-width: 900px)';

/* ---------- Small helpers ---------- */

const safeStorage = {
    get(storage, key) {
        try {
            return storage.getItem(key);
        } catch {
            return null;
        }
    },
    set(storage, key, value) {
        try {
            storage.setItem(key, value);
        } catch {
            /* Storage can be unavailable (private mode); the page still works without it. */
        }
    },
};

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function jsonHeaders() {
    return {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken(),
    };
}

function announce(message) {
    const announcer = document.getElementById('announcer');
    if (!announcer) {
        return;
    }
    announcer.textContent = '';
    window.setTimeout(() => {
        announcer.textContent = message;
    }, 50);
}

function plural(count, singular, pluralForm) {
    return `${count} ${count === 1 ? singular : pluralForm}`;
}

/* ---------- Page swaps ---------- */

/**
 * Fetch a logbook URL and replace <main> with its content, keeping client-side state
 * (collapsed continents, the filter text, edit mode).
 */
async function loadPage(url, { push = false, focusSelector = null } = {}) {
    let response;
    try {
        response = await fetch(url, { headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' } });
    } catch {
        window.location.href = url;
        return;
    }

    const html = await response.text();
    const nextDocument = new DOMParser().parseFromString(html, 'text/html');
    const nextMain = nextDocument.getElementById('logbook');
    const currentMain = document.getElementById('logbook');

    if (!response.ok || !nextMain || !currentMain) {
        window.location.href = url;
        return;
    }

    const filterText = document.querySelector('[data-index-filter]')?.value ?? '';
    currentMain.replaceWith(document.adoptNode(nextMain));
    document.title = nextDocument.title;

    if (push) {
        history.pushState({}, '', response.url);
    } else {
        history.replaceState({}, '', response.url);
    }

    restoreIndexState(filterText);

    if (focusSelector) {
        document.querySelector(focusSelector)?.focus();
    }
}

function showCountry(link) {
    const countryId = link.closest('[data-country-row]')?.dataset.id;
    const isNarrow = window.matchMedia(NARROW_LAYOUT).matches;

    loadPage(link.href, {
        push: true,
        focusSelector: isNarrow ? '#country-name' : `[data-country-row][data-id="${countryId}"] [data-country-link]`,
    }).then(() => {
        if (isNarrow) {
            document.getElementById('country-name')?.scrollIntoView({ block: 'start' });
        }
    });
}

window.addEventListener('popstate', () => loadPage(window.location.href));

/* ---------- Country index: collapse and filter ---------- */

function collapsedContinents() {
    try {
        return new Set(JSON.parse(safeStorage.get(localStorage, COLLAPSED_KEY) ?? '[]'));
    } catch {
        return new Set();
    }
}

function filterQuery() {
    return (document.querySelector('[data-index-filter]')?.value ?? '').trim().toLowerCase();
}

function applyIndexState() {
    const query = filterQuery();
    const collapsed = collapsedContinents();
    let anyMatch = false;

    document.querySelectorAll('[data-continent]').forEach((continent) => {
        const rows = [...continent.querySelectorAll('[data-country-row]')];
        let matches = 0;

        rows.forEach((row) => {
            const isMatch = !query || row.dataset.name.toLowerCase().includes(query);
            row.hidden = !isMatch;
            matches += isMatch ? 1 : 0;
        });

        anyMatch ||= matches > 0;
        continent.hidden = Boolean(query) && matches === 0;

        const count = continent.querySelector('[data-country-count]');
        if (count) {
            count.textContent = plural(query ? matches : Number(count.dataset.total), 'country', 'countries');
        }

        const isOpen = query ? true : !collapsed.has(continent.dataset.continent);
        const toggle = continent.querySelector('[data-continent-toggle]');
        toggle?.setAttribute('aria-expanded', String(isOpen));
        const list = document.getElementById(toggle?.getAttribute('aria-controls') ?? '');
        if (list) {
            list.hidden = !isOpen;
        }
    });

    const emptyMessage = document.querySelector('[data-filter-empty]');
    if (emptyMessage) {
        emptyMessage.hidden = !query || anyMatch;
    }
}

function restoreIndexState(filterText) {
    const input = document.querySelector('[data-index-filter]');
    if (input) {
        input.value = filterText;
    }
    applyIndexState();
}

function toggleContinent(toggle) {
    const id = toggle.closest('[data-continent]').dataset.continent;
    const collapsed = collapsedContinents();
    if (collapsed.has(id)) {
        collapsed.delete(id);
    } else {
        collapsed.add(id);
    }
    safeStorage.set(localStorage, COLLAPSED_KEY, JSON.stringify([...collapsed]));
    applyIndexState();
}

/* ---------- Places ---------- */

function selectPlace(placeId) {
    document.querySelectorAll('[data-place-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', String(button.dataset.placeToggle === placeId));
    });
    document.querySelectorAll('[data-place-detail]').forEach((detail) => {
        detail.hidden = detail.dataset.placeDetail !== placeId;
    });

    const url = new URL(window.location.href);
    if (placeId) {
        url.searchParams.set('place', placeId);
    } else {
        url.searchParams.delete('place');
    }
    history.replaceState({}, '', url);
}

/* ---------- Edit mode ---------- */

function setEditMode(isEditing) {
    document.documentElement.classList.toggle('is-editing', isEditing);
    safeStorage.set(sessionStorage, EDIT_MODE_KEY, isEditing ? '1' : '0');

    const toggle = document.querySelector('[data-edit-toggle]');
    if (toggle) {
        toggle.textContent = isEditing ? 'Done editing' : 'Edit';
    }
}

/* ---------- Edit dialog ---------- */

const dialog = document.getElementById('editor');
let refocusSelector = null;

async function openForm(url) {
    refocusSelector = `[data-open-form="${CSS.escape(url)}"]`;
    dialog.replaceChildren(Object.assign(document.createElement('p'), { className: 'editor__loading', textContent: 'Loading…' }));
    if (!dialog.open) {
        dialog.showModal();
    }

    try {
        const response = await fetch(url, { headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) {
            throw new Error(String(response.status));
        }
        dialog.innerHTML = await response.text();
    } catch {
        dialog.innerHTML = '<p class="editor__loading">This form could not be loaded. Check that the app is running. <button type="button" class="btn-text" data-editor-close>Close</button></p>';
        dialog.querySelector('[data-editor-close]')?.focus();
        return;
    }

    dialog.querySelectorAll('[data-list-editor]').forEach(relabelList);
    (dialog.querySelector('[data-autofocus]') ?? dialog.querySelector('input, select, textarea'))?.focus();
}

dialog?.addEventListener('close', () => dialog.replaceChildren());

/** Close the dialog, show the page the server pointed to, and put focus back where it was. */
async function finishEdit(redirectUrl, message) {
    dialog.close();
    const target = new URL(redirectUrl, window.location.href);
    await loadPage(target.href, { push: target.pathname !== window.location.pathname });

    const focusTarget = (refocusSelector && document.querySelector(refocusSelector)) || document.getElementById('country-name') || document.querySelector('[data-edit-toggle]');
    focusTarget?.focus();
    announce(message);
}

function clearErrors(form) {
    form.querySelectorAll('[data-error-for]').forEach((error) => {
        error.hidden = true;
        error.textContent = '';
    });
    form.querySelectorAll('[aria-invalid="true"]').forEach((control) => {
        control.removeAttribute('aria-invalid');
        const describedBy = (control.getAttribute('aria-describedby') ?? '').split(' ').filter((id) => id && !id.endsWith('-error'));
        if (describedBy.length) {
            control.setAttribute('aria-describedby', describedBy.join(' '));
        } else {
            control.removeAttribute('aria-describedby');
        }
    });
}

/** Show Laravel's 422 errors next to their fields. Keys like "facts.2" go to the list's error line. */
function showErrors(form, errors) {
    let firstInvalid = null;

    Object.entries(errors).forEach(([key, messages]) => {
        const [field, index] = key.split('.');
        const errorLine = form.querySelector(`[data-error-for="${CSS.escape(field)}"]`) ?? form.querySelector('[data-error-for="_form"]');
        if (!errorLine) {
            return;
        }

        let text = messages[0];
        if (index !== undefined) {
            const itemLabel = errorLine.closest('[data-list-editor]')?.dataset.itemLabel ?? 'Item';
            text = `${itemLabel} ${Number(index) + 1}: ${text}`;
        }
        errorLine.textContent = errorLine.textContent ? `${errorLine.textContent} ${text}` : text;
        errorLine.hidden = false;

        const controls = [...form.querySelectorAll(`[name="${CSS.escape(field)}"], [name="${CSS.escape(field)}[]"]`)];
        const invalid = index !== undefined ? controls.slice(Number(index), Number(index) + 1) : controls;
        invalid.forEach((control) => {
            control.setAttribute('aria-invalid', 'true');
            if (errorLine.id) {
                const describedBy = new Set((control.getAttribute('aria-describedby') ?? '').split(' ').filter(Boolean));
                describedBy.add(errorLine.id);
                control.setAttribute('aria-describedby', [...describedBy].join(' '));
            }
        });

        firstInvalid ??= invalid[0] ?? null;
    });

    if (firstInvalid) {
        firstInvalid.focus();
        return;
    }
    const errorLine = form.querySelector('[data-error-for]:not([hidden])');
    errorLine?.setAttribute('tabindex', '-1');
    errorLine?.focus();
}

async function sendForm(form, url, body, successMessage) {
    const buttons = [...form.querySelectorAll('button')];
    buttons.forEach((button) => {
        button.disabled = true;
    });
    clearErrors(form);

    try {
        const response = await fetch(url, { method: 'POST', body, headers: jsonHeaders() });

        if (response.status === 422) {
            showErrors(form, (await response.json()).errors ?? {});
            return false;
        }
        if (!response.ok) {
            showErrors(form, { _form: ['Something went wrong while saving. Please try again.'] });
            return false;
        }

        const { redirect } = await response.json();
        await finishEdit(redirect ?? window.location.href, successMessage);
        return true;
    } catch {
        showErrors(form, { _form: ['Could not reach the server. Check that the app is running, then try again.'] });
        return false;
    } finally {
        buttons.forEach((button) => {
            button.disabled = false;
        });
    }
}

dialog?.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-editor-form]');
    if (!form) {
        return;
    }
    event.preventDefault();
    sendForm(form, form.action, new FormData(form), 'Saved.');
});

function askToDelete(button) {
    const footer = button.closest('.editor__foot');
    footer.dataset.restore = footer.innerHTML;
    footer.dataset.pendingDeleteUrl = button.dataset.deleteAction;

    const message = Object.assign(document.createElement('p'), { className: 'editor__confirm', textContent: button.dataset.deleteConfirm });
    message.setAttribute('role', 'alert');
    const actions = Object.assign(document.createElement('div'), { className: 'editor__foot-end' });
    const keep = Object.assign(document.createElement('button'), { type: 'button', className: 'btn-outline', textContent: 'Keep it' });
    keep.dataset.deleteCancel = '';
    const confirm = Object.assign(document.createElement('button'), { type: 'button', className: 'btn-danger', textContent: 'Delete' });
    confirm.dataset.deleteConfirmed = '';
    actions.append(keep, confirm);

    footer.replaceChildren(message, actions);
    keep.focus();
}

function cancelDelete(footer) {
    footer.innerHTML = footer.dataset.restore ?? '';
    delete footer.dataset.restore;
    delete footer.dataset.pendingDeleteUrl;
    footer.querySelector('[data-delete-action]')?.focus();
}

async function confirmDelete(footer) {
    const form = footer.closest('form');
    const body = new FormData();
    body.append('_token', csrfToken());
    body.append('_method', 'DELETE');

    const deleted = await sendForm(form, footer.dataset.pendingDeleteUrl, body, 'Deleted.');
    if (!deleted && footer.isConnected) {
        cancelDelete(footer);
    }
}

/* ---------- List editors (facts, region countries) ---------- */

function listItems(editor) {
    return [...editor.querySelector('[data-list-items]').children];
}

function relabelList(editor) {
    const label = editor.dataset.itemLabel;
    const items = listItems(editor);

    items.forEach((item, index) => {
        const number = index + 1;
        item.querySelector('[data-list-input]')?.setAttribute('aria-label', `${label} ${number}`);
        item.querySelector('[data-list-remove]')?.setAttribute('aria-label', `Remove ${label.toLowerCase()} ${number}`);

        const up = item.querySelector('[data-list-up]');
        if (up) {
            up.setAttribute('aria-label', `Move ${label.toLowerCase()} ${number} up`);
            up.disabled = index === 0;
        }
        const down = item.querySelector('[data-list-down]');
        if (down) {
            down.setAttribute('aria-label', `Move ${label.toLowerCase()} ${number} down`);
            down.disabled = index === items.length - 1;
        }
    });
}

function addListItem(editor, afterItem = null) {
    const item = editor.querySelector('[data-list-template]').content.firstElementChild.cloneNode(true);
    if (afterItem) {
        afterItem.after(item);
    } else {
        editor.querySelector('[data-list-items]').append(item);
    }
    relabelList(editor);
    item.querySelector('[data-list-input]')?.focus();
}

function removeListItem(item) {
    const editor = item.closest('[data-list-editor]');
    const next = item.nextElementSibling ?? item.previousElementSibling;
    item.remove();
    relabelList(editor);
    (next?.querySelector('[data-list-input]') ?? editor.querySelector('[data-list-add]'))?.focus();
}

function moveListItem(item, direction) {
    const editor = item.closest('[data-list-editor]');
    if (direction < 0 && item.previousElementSibling) {
        item.previousElementSibling.before(item);
    } else if (direction > 0 && item.nextElementSibling) {
        item.nextElementSibling.after(item);
    }
    relabelList(editor);

    const sameButton = item.querySelector(direction < 0 ? '[data-list-up]' : '[data-list-down]');
    (sameButton && !sameButton.disabled ? sameButton : item.querySelector('[data-list-input]'))?.focus();
    announce(`${editor.dataset.itemLabel} moved to position ${listItems(editor).indexOf(item) + 1}.`);
}

dialog?.addEventListener('keydown', (event) => {
    const input = event.target.closest('input[data-list-input]');
    if (input && event.key === 'Enter') {
        event.preventDefault();
        addListItem(input.closest('[data-list-editor]'), input.closest('[data-list-item]'));
    }
});

/* ---------- Reordering (drag handles) ---------- */

function sortItems(list) {
    return [...list.children].filter((element) => element.matches('[data-sort-item]'));
}

function sortOrder(list) {
    return sortItems(list).map((item) => Number(item.dataset.id));
}

async function saveOrder(list, previousOrder) {
    const ids = sortOrder(list);
    if (ids.join() === previousOrder.join()) {
        return;
    }

    try {
        const response = await fetch(list.dataset.sortUrl, {
            method: 'PUT',
            headers: { ...jsonHeaders(), 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids }),
        });
        if (!response.ok) {
            throw new Error(String(response.status));
        }
    } catch {
        announce('The new order could not be saved. The list has been reset.');
        await loadPage(window.location.href);
    }
}

function announcePosition(item) {
    const visible = sortItems(item.parentElement).filter((element) => !element.hidden);
    announce(`${item.dataset.name}, position ${visible.indexOf(item) + 1} of ${visible.length}.`);
}

let drag = null;

document.addEventListener('pointerdown', (event) => {
    const handle = event.target.closest('[data-sort-handle]');
    if (!handle || event.button !== 0) {
        return;
    }
    const item = handle.closest('[data-sort-item]');
    event.preventDefault();
    handle.setPointerCapture(event.pointerId);
    drag = { item, list: item.parentElement, pointerId: event.pointerId, previousOrder: sortOrder(item.parentElement) };
    item.classList.add('is-dragging');
});

document.addEventListener('pointermove', (event) => {
    if (!drag || event.pointerId !== drag.pointerId) {
        return;
    }
    const siblings = sortItems(drag.list).filter((element) => element !== drag.item && !element.hidden);
    const before = siblings.find((element) => {
        const box = element.getBoundingClientRect();
        return event.clientY < box.top + box.height / 2;
    });

    if (before) {
        if (drag.item.nextElementSibling !== before) {
            before.before(drag.item);
        }
    } else if (siblings.length && siblings.at(-1).nextElementSibling !== drag.item) {
        siblings.at(-1).after(drag.item);
    }
});

function endDrag(event) {
    if (!drag || event.pointerId !== drag.pointerId) {
        return;
    }
    const { item, list, previousOrder } = drag;
    drag = null;
    item.classList.remove('is-dragging');

    if (sortOrder(list).join() !== previousOrder.join()) {
        announcePosition(item);
        saveOrder(list, previousOrder);
    }
}

document.addEventListener('pointerup', endDrag);
document.addEventListener('pointercancel', endDrag);

const pendingKeyboardSaves = new Map();

document.addEventListener('keydown', (event) => {
    const handle = event.target.closest('[data-sort-handle]');
    if (!handle || (event.key !== 'ArrowUp' && event.key !== 'ArrowDown')) {
        return;
    }
    event.preventDefault();

    const item = handle.closest('[data-sort-item]');
    const list = item.parentElement;
    const visible = sortItems(list).filter((element) => !element.hidden);
    const index = visible.indexOf(item);
    const target = visible[index + (event.key === 'ArrowUp' ? -1 : 1)];
    if (!target) {
        return;
    }

    const pending = pendingKeyboardSaves.get(list) ?? { previousOrder: sortOrder(list) };
    window.clearTimeout(pending.timer);

    if (event.key === 'ArrowUp') {
        target.before(item);
    } else {
        target.after(item);
    }
    handle.focus();
    announcePosition(item);

    pending.timer = window.setTimeout(() => {
        pendingKeyboardSaves.delete(list);
        saveOrder(list, pending.previousOrder);
    }, 600);
    pendingKeyboardSaves.set(list, pending);
});

/* ---------- Click routing ---------- */

document.addEventListener('click', (event) => {
    const target = event.target;

    const countryLink = target.closest('[data-country-link]');
    if (countryLink) {
        if (event.button === 0 && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey) {
            event.preventDefault();
            showCountry(countryLink);
        }
        return;
    }

    const continentToggle = target.closest('[data-continent-toggle]');
    if (continentToggle) {
        toggleContinent(continentToggle);
        return;
    }

    const placeToggle = target.closest('[data-place-toggle]');
    if (placeToggle) {
        const isOpen = placeToggle.getAttribute('aria-pressed') === 'true';
        selectPlace(isOpen ? null : placeToggle.dataset.placeToggle);
        return;
    }

    if (target.closest('[data-edit-toggle]')) {
        const isEditing = !document.documentElement.classList.contains('is-editing');
        setEditMode(isEditing);
        announce(isEditing ? 'Edit mode on.' : 'Edit mode off.');
        return;
    }

    const formOpener = target.closest('[data-open-form]');
    if (formOpener) {
        openForm(formOpener.dataset.openForm);
        return;
    }

    if (target.closest('[data-editor-close]')) {
        dialog.close();
        return;
    }

    const deleteButton = target.closest('[data-delete-action]');
    if (deleteButton) {
        askToDelete(deleteButton);
        return;
    }
    if (target.closest('[data-delete-cancel]')) {
        cancelDelete(target.closest('.editor__foot'));
        return;
    }
    if (target.closest('[data-delete-confirmed]')) {
        confirmDelete(target.closest('.editor__foot'));
        return;
    }

    const listAdd = target.closest('[data-list-add]');
    if (listAdd) {
        addListItem(listAdd.closest('[data-list-editor]'));
        return;
    }
    const listRemove = target.closest('[data-list-remove]');
    if (listRemove) {
        removeListItem(listRemove.closest('[data-list-item]'));
        return;
    }
    const listUp = target.closest('[data-list-up]');
    if (listUp) {
        moveListItem(listUp.closest('[data-list-item]'), -1);
        return;
    }
    const listDown = target.closest('[data-list-down]');
    if (listDown) {
        moveListItem(listDown.closest('[data-list-item]'), 1);
    }
});

document.addEventListener('input', (event) => {
    if (event.target.matches('[data-index-filter]')) {
        applyIndexState();
    }
});

/* ---------- Start ---------- */

setEditMode(safeStorage.get(sessionStorage, EDIT_MODE_KEY) === '1');
applyIndexState();
