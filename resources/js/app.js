import Alpine from 'alpinejs';

/**
 * Invoice form: line items, extra charges and discounts with live totals.
 * The server recalculates every amount on save; these numbers are only a preview.
 */
Alpine.data('invoiceForm', (initial = {}) => ({
    items: initial.items?.length ? initial.items : [{ item: '', category: '', quantity: 1, price: 0, discount: 0 }],
    charges: initial.charges ?? [],
    discounts: initial.discounts ?? [],
    enableTax: initial.enableTax ?? false,
    taxRate: Number(initial.taxRate ?? 0),
    roundOff: Number(initial.roundOff ?? 0),
    isRecurring: initial.isRecurring ?? false,

    addItem() {
        this.items.push({ item: '', category: '', quantity: 1, price: 0, discount: 0 });
    },
    copyItem(index) {
        this.items.splice(index + 1, 0, { ...this.items[index] });
    },
    removeItem(index) {
        if (this.items.length > 1) this.items.splice(index, 1);
    },
    lineAmount(row) {
        const gross = Number(row.quantity || 0) * Number(row.price || 0);
        return gross - (gross * Number(row.discount || 0)) / 100;
    },
    get subtotal() {
        return this.items.reduce((sum, row) => sum + this.lineAmount(row), 0);
    },
    get chargesTotal() {
        return this.charges.reduce((sum, value) => sum + Number(value || 0), 0);
    },
    get discountTotal() {
        return this.discounts.reduce((sum, percent) => sum + (this.subtotal * Number(percent || 0)) / 100, 0);
    },
    get taxAmount() {
        return this.enableTax ? (this.subtotal * this.taxRate) / 100 : 0;
    },
    get total() {
        return this.subtotal + this.taxAmount + this.chargesTotal - this.discountTotal + Number(this.roundOff || 0);
    },
    money(value) {
        return Number(value).toFixed(2);
    },
}));

/**
 * Toast notifications. Server flashes are seeded by <x-flash>; anything else can raise one with
 *   window.toast('Saved.', 'success')            or  toast({ type, title, message, timeout })
 *   $dispatch('toast', { type: 'error', message: '...' })   from any Alpine component
 * timeout: 0 keeps the toast open until dismissed.
 */
const TOAST_TYPES = {
    success: { timeout: 3500 },
    error: { timeout: 7000 },
    warning: { timeout: 5000 },
    info: { timeout: 4000 },
};
const TOAST_LIMIT = 5;
let toastId = 0;

Alpine.store('toasts', {
    items: [],

    push(input) {
        const options = typeof input === 'string' ? { message: input } : { ...input };
        const type = TOAST_TYPES[options.type] ? options.type : 'info';
        const toast = {
            id: ++toastId,
            type,
            title: options.title ?? '', // optional; most toasts are one line
            message: options.message ?? '',
            details: options.details ?? [],
            timeout: options.timeout ?? TOAST_TYPES[type].timeout,
            visible: true,
            remaining: 0,
            startedAt: 0,
            timer: null,
        };
        this.items.push(toast);
        while (this.items.length > TOAST_LIMIT) this.items.shift();
        if (toast.timeout > 0) this.resume(toast.id, toast.timeout);
        return toast.id;
    },
    find(id) {
        return this.items.find((toast) => toast.id === id);
    },
    pause(id) {
        const toast = this.find(id);
        if (!toast?.timer) return;
        clearTimeout(toast.timer);
        toast.timer = null;
        toast.remaining -= Date.now() - toast.startedAt;
    },
    resume(id, duration) {
        const toast = this.find(id);
        if (!toast || toast.timeout <= 0 || toast.timer) return;
        toast.remaining = duration ?? toast.remaining;
        toast.startedAt = Date.now();
        toast.timer = setTimeout(() => this.dismiss(id), Math.max(toast.remaining, 0));
    },
    dismiss(id) {
        const toast = this.find(id);
        if (!toast) return;
        clearTimeout(toast.timer);
        toast.visible = false; // let the leave transition play, then drop it
        setTimeout(() => (this.items = this.items.filter((item) => item.id !== id)), 300);
    },
    clear() {
        this.items.forEach((toast) => this.dismiss(toast.id));
    },
});

window.toast = (message, type = 'info', options = {}) =>
    Alpine.store('toasts').push(typeof message === 'object' ? message : { ...options, message, type });
window.addEventListener('toast', (event) => window.toast(event.detail));

/**
 * Custom dropdown for <x-form.select>. A real <select> stays underneath as the source of truth, so
 * form posts, `required`, x-model and onchange="this.form.submit()" all keep working unchanged:
 * options are read from it, and picking one writes back to it and fires input + change.
 */
Alpine.data('selectMenu', (config = {}) => ({
    open: false,
    query: '',
    opts: [],
    current: '',
    active: -1,
    panelStyle: '',
    isDisabled: false,
    isRequired: false,
    searchable: config.searchable ?? null,

    init() {
        const native = this.$refs.native;
        // mirror the real select's disabled/required, which x-bind may toggle later
        const syncState = () => {
            this.isDisabled = native.disabled;
            this.isRequired = native.required;
        };
        syncState();
        new MutationObserver(syncState).observe(native, { attributes: true, attributeFilter: ['disabled', 'required'] });
        this.$nextTick(() => this.read()); // let x-model on the native select set its value first
        native.addEventListener('change', () => this.read());
        native.form?.addEventListener('reset', () => setTimeout(() => this.read()));
        if (config.groupFilter) {
            // dependent dropdown: when the allowed group changes, drop a selection that no longer fits
            Alpine.effect(() => {
                const group = config.groupFilter();
                this.$nextTick(() => {
                    const chosen = this.selected;
                    if (chosen && chosen.group && group && chosen.group !== group) this.clear(false);
                });
            });
        }
        const reposition = () => this.open && this.place();
        window.addEventListener('scroll', reposition, true);
        window.addEventListener('resize', reposition);
    },
    read() {
        const native = this.$refs.native;
        this.opts = [...native.options].map((option) => ({
            value: option.value,
            label: option.text.trim(),
            group: option.parentElement.tagName === 'OPTGROUP' ? option.parentElement.label : null,
            disabled: option.disabled,
            placeholder: option.value === '',
        }));
        this.current = native.value;
        if (config.searchable === null || config.searchable === undefined) this.searchable = this.opts.length > 8;
    },
    get disabled() {
        return this.isDisabled;
    },
    get selected() {
        return this.opts.find((option) => option.value === this.current && !option.placeholder) ?? null;
    },
    get placeholder() {
        return this.opts.find((option) => option.placeholder)?.label ?? 'Select...';
    },
    get clearable() {
        return !this.isRequired && this.opts.some((option) => option.placeholder) && this.selected;
    },
    get choices() {
        // the empty "placeholder" option is only offered when the field is optional ("-- None --")
        const q = this.query.trim().toLowerCase();
        const group = config.groupFilter ? config.groupFilter() : null;
        return this.opts.filter(
            (option) =>
                (!option.placeholder || !this.isRequired) &&
                (!group || option.placeholder || option.group === group) &&
                (!q || option.label.toLowerCase().includes(q) || (option.group ?? '').toLowerCase().includes(q)),
        );
    },
    get rows() {
        const rows = [];
        let group = null;
        this.choices.forEach((option, index) => {
            if (option.group && option.group !== group) rows.push({ key: `g:${option.group}`, heading: option.group });
            group = option.group;
            rows.push({ key: `o:${option.value}`, option, index });
        });
        return rows;
    },
    toggle() {
        this.open ? this.close() : this.show();
    },
    show() {
        if (this.disabled) return;
        this.read();
        this.query = '';
        this.active = Math.max(0, this.choices.findIndex((option) => option.value === this.current));
        this.open = true;
        this.place();
        this.$nextTick(() => {
            (this.searchable ? this.$refs.search : this.$refs.list)?.focus({ preventScroll: true });
            this.scrollToActive();
        });
    },
    close(refocus = false) {
        this.open = false;
        if (refocus) this.$refs.button.focus();
    },
    place() {
        const rect = this.$refs.button.getBoundingClientRect();
        const width = Math.max(rect.width, 224);
        const left = Math.max(8, Math.min(rect.left, window.innerWidth - width - 8));
        const below = window.innerHeight - rect.bottom;
        const vertical = below < 300 && rect.top > below ? `bottom:${window.innerHeight - rect.top + 6}px` : `top:${rect.bottom + 6}px`;
        this.panelStyle = `position:fixed;left:${left}px;width:${width}px;${vertical}`;
    },
    pick(option, refocus = true) {
        if (!option || option.disabled) return;
        const native = this.$refs.native;
        const changed = native.value !== option.value;
        native.value = option.value;
        this.current = option.value;
        if (changed) {
            native.dispatchEvent(new Event('input', { bubbles: true }));
            native.dispatchEvent(new Event('change', { bubbles: true }));
        }
        this.close(refocus);
    },
    clear(refocus = true) {
        const empty = this.opts.find((option) => option.placeholder) ?? { value: '', label: '' };
        this.pick(empty, refocus);
    },
    move(step) {
        const count = this.choices.length;
        if (!count) return;
        let next = this.active;
        for (let i = 0; i < count; i++) {
            next = (next + step + count) % count;
            if (!this.choices[next].disabled) break;
        }
        this.active = next;
        this.scrollToActive();
    },
    scrollToActive() {
        this.$nextTick(() => this.$refs.list?.querySelector('[data-active="true"]')?.scrollIntoView({ block: 'nearest' }));
    },
    onButtonKey(event) {
        if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
            event.preventDefault();
            this.show();
        }
    },
    onPanelKey(event) {
        const keys = {
            ArrowDown: () => this.move(1),
            ArrowUp: () => this.move(-1),
            Home: () => ((this.active = -1), this.move(1)),
            End: () => ((this.active = 0), this.move(-1)),
            Enter: () => this.pick(this.choices[this.active]),
            Escape: () => this.close(true),
            Tab: () => this.close(),
        };
        if (!keys[event.key]) return;
        if (event.key !== 'Tab') event.preventDefault();
        keys[event.key]();
    },
}));

/**
 * Calendar for <x-form.input type="date|month">. Like selectMenu, a real <input type="date|month">
 * stays underneath and holds the value, so min/max/required, onchange and posts work unchanged.
 * config.after names another field in the same form whose value is the earliest allowed date
 * (e.g. an end date that can't be before the start date).
 */
const pad = (n) => String(n).padStart(2, '0');
const isoDate = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;
const MONTHS_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const MONTHS_LONG = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

Alpine.data('datePicker', (config = {}) => ({
    mode: config.mode === 'month' ? 'month' : 'date',
    open: false,
    view: 'days', // days | months | years
    year: 0,
    month: 0,
    active: '',
    value: '',
    min: '',
    max: '',
    isDisabled: false,
    isRequired: false,
    panelStyle: '',

    init() {
        const native = this.$refs.native;
        const syncState = () => {
            this.isDisabled = native.disabled;
            this.isRequired = native.required;
        };
        syncState();
        new MutationObserver(syncState).observe(native, { attributes: true, attributeFilter: ['disabled', 'required'] });
        this.$nextTick(() => (this.value = native.value));
        native.addEventListener('change', () => (this.value = native.value));
        native.form?.addEventListener('reset', () => setTimeout(() => (this.value = native.value)));
        const reposition = () => this.open && this.place();
        window.addEventListener('scroll', reposition, true);
        window.addEventListener('resize', reposition);
    },

    // ----- display -------------------------------------------------------------------------
    get label() {
        if (!this.value) return '';
        const [y, m, d] = this.value.split('-').map(Number);
        return this.mode === 'month' ? `${MONTHS_LONG[m - 1]} ${y}` : `${pad(d)} ${MONTHS_SHORT[m - 1]} ${y}`;
    },
    get today() {
        const now = new Date();
        return isoDate(now.getFullYear(), now.getMonth(), now.getDate());
    },
    get heading() {
        if (this.view === 'days') return `${MONTHS_LONG[this.month]} ${this.year}`;
        if (this.view === 'months') return String(this.year);
        return `${this.yearStart} – ${this.yearStart + 11}`;
    },
    get yearStart() {
        return this.year - (((this.year % 12) + 12) % 12);
    },
    get days() {
        const first = new Date(this.year, this.month, 1);
        const offset = (first.getDay() + 6) % 7; // weeks start on Monday
        return Array.from({ length: 42 }, (_, i) => {
            const date = new Date(this.year, this.month, 1 - offset + i);
            const iso = isoDate(date.getFullYear(), date.getMonth(), date.getDate());
            return { iso, day: date.getDate(), outside: date.getMonth() !== this.month, disabled: this.outOfRange(iso) };
        });
    },
    get months() {
        return MONTHS_SHORT.map((name, index) => {
            const iso = `${this.year}-${pad(index + 1)}`;
            return { index, name, iso, disabled: this.monthOutOfRange(iso) };
        });
    },
    get years() {
        return Array.from({ length: 12 }, (_, i) => this.yearStart + i);
    },
    outOfRange(iso) {
        return Boolean((this.min && iso < this.min) || (this.max && iso > this.max));
    },
    monthOutOfRange(ym) {
        // a month is selectable when any part of it is inside min/max
        return Boolean((this.min && ym < this.min.slice(0, 7)) || (this.max && ym > this.max.slice(0, 7)));
    },
    isSelectedMonth(index) {
        return this.value.slice(0, 7) === `${this.year}-${pad(index + 1)}`;
    },

    // ----- open / close --------------------------------------------------------------------
    toggle() {
        this.open ? this.close() : this.show();
    },
    show() {
        if (this.isDisabled) return;
        const native = this.$refs.native;
        this.value = native.value;
        this.min = native.min || '';
        this.max = native.max || '';
        const after = config.after && native.form?.querySelector(`[name="${config.after}"]`)?.value;
        if (after && (!this.min || after > this.min)) this.min = after;

        const start = this.value || (this.min && this.today < this.min ? this.min : this.max && this.today > this.max ? this.max : this.today);
        const [y, m] = start.split('-').map(Number);
        this.year = y;
        this.month = m - 1;
        this.active = this.mode === 'month' ? start.slice(0, 7) : start.length === 7 ? `${start}-01` : start;
        this.view = this.mode === 'month' ? 'months' : 'days';
        this.open = true;
        this.place();
        this.$nextTick(() => this.$refs.panel?.focus({ preventScroll: true }));
    },
    close(refocus = false) {
        this.open = false;
        if (refocus) this.$refs.button.focus();
    },
    place() {
        const rect = this.$refs.button.getBoundingClientRect();
        const width = 288;
        const left = Math.max(8, Math.min(rect.left, window.innerWidth - width - 8));
        const below = window.innerHeight - rect.bottom;
        const vertical = below < 370 && rect.top > below ? `bottom:${window.innerHeight - rect.top + 6}px` : `top:${rect.bottom + 6}px`;
        this.panelStyle = `position:fixed;left:${left}px;width:${width}px;${vertical}`;
    },

    // ----- navigation ----------------------------------------------------------------------
    step(direction) {
        if (this.view === 'days') {
            const date = new Date(this.year, this.month + direction, 1);
            this.year = date.getFullYear();
            this.month = date.getMonth();
        } else {
            this.year += direction * (this.view === 'months' ? 1 : 12);
        }
    },
    zoomOut() {
        this.view = this.view === 'days' ? 'months' : 'years';
    },
    chooseYear(year) {
        this.year = year;
        this.view = 'months';
    },
    chooseMonth(month) {
        if (month.disabled) return;
        if (this.mode === 'month') return this.pick(month.iso);
        this.month = month.index;
        this.view = 'days';
    },

    // ----- picking -------------------------------------------------------------------------
    pick(iso, refocus = true) {
        if (iso && (this.mode === 'month' ? this.monthOutOfRange(iso) : this.outOfRange(iso))) return;
        const native = this.$refs.native;
        const changed = native.value !== iso;
        native.value = iso;
        this.value = iso;
        if (changed) {
            native.dispatchEvent(new Event('input', { bubbles: true }));
            native.dispatchEvent(new Event('change', { bubbles: true }));
        }
        this.close(refocus);
    },
    pickToday() {
        this.pick(this.mode === 'month' ? this.today.slice(0, 7) : this.today);
    },
    get todayAllowed() {
        return this.mode === 'month' ? !this.monthOutOfRange(this.today.slice(0, 7)) : !this.outOfRange(this.today);
    },
    clear() {
        this.pick('');
    },
    onButtonKey(event) {
        if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
            event.preventDefault();
            this.show();
        }
    },
    onPanelKey(event) {
        if (event.key === 'Escape') return event.preventDefault(), this.close(true);
        if (event.key === 'Tab') return this.close();
        if (this.view !== 'days') return;
        const moves = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 };
        if (moves[event.key] !== undefined || event.key === 'PageUp' || event.key === 'PageDown') {
            event.preventDefault();
            const [y, m, d] = this.active.split('-').map(Number);
            const next = moves[event.key] !== undefined ? new Date(y, m - 1, d + moves[event.key]) : new Date(y, m - 1 + (event.key === 'PageUp' ? -1 : 1), d);
            this.active = isoDate(next.getFullYear(), next.getMonth(), next.getDate());
            this.year = next.getFullYear();
            this.month = next.getMonth();
        } else if (event.key === 'Enter') {
            event.preventDefault();
            this.pick(this.active);
        }
    },
}));

/**
 * Live tables. On a page with an <x-table live> (marked [data-live-region]):
 *  - GET forms marked [data-live] (the search bar, filters, rows-per-page) reload just the
 *    regions: on submit, on change, and while typing in a search box (debounced);
 *  - links marked [data-live-link] inside a region (sort headers, pagination) do the same.
 * The new page is fetched as normal HTML and every [data-live-region="key"] is swapped for its
 * fresh copy; the URL is updated, so refresh, back/forward and shared links all still work.
 * Without a live region, or without JavaScript, the same forms and links simply load the page.
 */
const liveRegions = () => document.querySelectorAll('[data-live-region]');
let liveRequest = null;

async function liveLoad(url, { history: mode = 'push' } = {}) {
    liveRequest?.abort();
    const request = (liveRequest = new AbortController());
    const regions = liveRegions();
    regions.forEach((region) => region.setAttribute('aria-busy', 'true'));
    try {
        const response = await fetch(url, { signal: request.signal, headers: { Accept: 'text/html' } });
        // a redirect (e.g. the session expired) or an error page: hand over to a normal page load
        if (!response.ok || response.redirected) return void (window.location.href = response.url || url);
        const page = new DOMParser().parseFromString(await response.text(), 'text/html');
        regions.forEach((region) => {
            const fresh = page.querySelector(`[data-live-region="${region.dataset.liveRegion}"]`);
            if (fresh) region.replaceWith(document.importNode(fresh, true));
        });
        if (mode === 'push') window.history.pushState({ live: true }, '', url);
        if (mode === 'replace') window.history.replaceState({ live: true }, '', url);
    } catch (error) {
        if (error.name !== 'AbortError') window.location.href = url;
    } finally {
        if (liveRequest === request) {
            liveRequest = null;
            liveRegions().forEach((region) => region.removeAttribute('aria-busy'));
        }
    }
}

const liveFormUrl = (form) => {
    const url = new URL(form.getAttribute('action') || window.location.pathname, window.location.href);
    // keep what the form doesn't control (sort, page size, other filters); replace what it does
    const params = new URLSearchParams(window.location.search);
    const bases = [...form.elements].map((el) => el.name).filter(Boolean).map((name) => name.replace(/\[\]$/, ''));
    [...params.keys()].forEach((key) => {
        if (bases.some((base) => key === base || key === `${base}[]` || key.startsWith(`${base}[`))) params.delete(key);
    });
    params.delete('page'); // a new search or filter starts again from the first page
    new FormData(form).forEach((value, key) => value !== '' && params.append(key, value));
    url.search = params.toString();
    return url.toString();
};

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-live]');
    if (!form || !liveRegions().length || event.defaultPrevented) return;
    event.preventDefault();
    liveLoad(liveFormUrl(form));
});

document.addEventListener('change', (event) => {
    const form = event.target.closest?.('form[data-live]');
    if (!form || event.target.type === 'search' || event.target.type === 'text') return;
    liveRegions().length ? liveLoad(liveFormUrl(form)) : form.requestSubmit();
});

let liveTyping = null;
document.addEventListener('input', (event) => {
    const form = event.target.closest?.('form[data-live]');
    if (!form || event.target.type !== 'search' || !liveRegions().length) return;
    clearTimeout(liveTyping);
    liveTyping = setTimeout(() => liveLoad(liveFormUrl(form), { history: 'replace' }), 350);
});

document.addEventListener('click', (event) => {
    const link = event.target.closest('a[data-live-link]');
    if (!link || !link.closest('[data-live-region]') || event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) return;
    event.preventDefault();
    liveLoad(link.href);
});

// back/forward through live states: the filter forms outside the table must match the URL too
window.addEventListener('popstate', (event) => {
    if (event.state?.live || liveRegions().length) window.location.reload();
});

/**
 * Image upload with preview (<x-form.image-upload>): pick or drop a file, see it at once, undo.
 * Wrong types and files over the size limit are rejected in the browser with a toast, before
 * anything is sent. The chosen image is also broadcast as `image-preview` (detail: { name, url })
 * so other parts of the page, such as a summary card, can show it too.
 */
Alpine.data('imageUpload', (config = {}) => ({
    current: config.current ?? null,
    preview: null,
    fileName: '',
    fileSize: '',
    dragging: false,

    get shown() {
        return this.preview ?? this.current;
    },
    choose() {
        this.$refs.input.click();
    },
    onChange() {
        const file = this.$refs.input.files[0];
        if (!file) return this.reset();
        if (!file.type.startsWith('image/')) return this.reject('Please choose an image file (JPG, PNG or GIF).');
        if (file.size > config.maxKb * 1024) return this.reject(`The image is too large. The limit is ${config.maxKb / 1024} MB.`);
        if (this.preview) URL.revokeObjectURL(this.preview);
        this.preview = URL.createObjectURL(file);
        this.fileName = file.name;
        this.fileSize = file.size > 1024 * 1024 ? `${(file.size / 1024 / 1024).toFixed(1)} MB` : `${Math.ceil(file.size / 1024)} KB`;
        this.$dispatch('image-preview', { name: config.name, url: this.preview });
    },
    onDrop(event) {
        this.dragging = false;
        const files = event.dataTransfer?.files;
        if (!files?.length) return;
        this.$refs.input.files = files;
        this.onChange();
    },
    reject(message) {
        window.toast(message, 'error');
        this.reset();
    },
    reset() {
        this.$refs.input.value = '';
        if (this.preview) URL.revokeObjectURL(this.preview);
        this.preview = null;
        this.fileName = '';
        this.fileSize = '';
        this.$dispatch('image-preview', { name: config.name, url: this.current });
    },
}));

/**
 * Confirmation dialog (<x-confirm-dialog> in the app layout). Any button or link with
 * data-confirm="message" asks first (optional data-confirm-title, -label and -tone = danger|primary),
 * then carries on: a submit button submits its form, a link is followed.
 * From JavaScript: if (await window.confirmAction({ title, message, confirmLabel, tone })) { ... }
 */
/* <x-form-progress>: how many required fields of a form are filled in (radios count once per group). */
Alpine.data('formProgress', (formId) => ({
    done: 0,
    total: 0,
    get complete() {
        return this.total > 0 && this.done === this.total;
    },
    init() {
        const form = document.getElementById(formId);
        if (!form) return;
        const update = () => {
            const groups = new Map();
            form.querySelectorAll('[required]').forEach((field) => {
                // disabled fields still count: a section waiting for its class is still to be filled in
                if (field.type === 'hidden' || !field.name) return;
                const filled = field.type === 'radio' || field.type === 'checkbox' ? field.checked : String(field.value).trim() !== '';
                groups.set(field.name, (groups.get(field.name) ?? false) || filled);
            });
            this.total = groups.size;
            this.done = [...groups.values()].filter(Boolean).length;
        };
        ['input', 'change'].forEach((type) => form.addEventListener(type, () => setTimeout(update)));
        // custom selects and pickers set their values after start-up; disabled fields change too
        new MutationObserver(() => setTimeout(update)).observe(form, { subtree: true, attributes: true, attributeFilter: ['disabled', 'required'] });
        setTimeout(update, 50);
    },
}));

/* Keep Tab and Shift+Tab inside an open dialog. */
const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]):not([type=hidden]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
window.trapFocus = (container, event) => {
    const items = [...container.querySelectorAll(focusableSelector)].filter((el) => el.offsetParent !== null && !el.closest('.sr-only, [inert]'));
    if (!items.length) return;
    const [first, last] = [items[0], items.at(-1)];
    if (document.activeElement === container) {
        // the dialog itself has focus (opened with the mouse): Tab enters at the start, Shift+Tab at the end
        event.preventDefault();
        (event.shiftKey ? last : first).focus();
    } else if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
};

/* Whether the last input was a mouse/touch press rather than a key, so dialogs opened by a click
   don't light up a keyboard focus outline (see the confirm dialog). */
let pointerInput = false;
document.addEventListener('pointerdown', () => (pointerInput = true), true);
document.addEventListener('keydown', () => (pointerInput = false), true);
window.usedPointer = () => pointerInput;

/* Page scroll is locked while any dialog is open (counted, so stacked dialogs unlock in order). */
let openDialogs = 0;
window.lockScroll = (lock) => {
    openDialogs = Math.max(0, openDialogs + (lock ? 1 : -1));
    document.documentElement.style.overflow = openDialogs ? 'hidden' : '';
};

/**
 * <x-modal>: opened with $dispatch('open-modal', name) or ('open-modal', { name, fill: { field: value } }),
 * closed with Esc, the backdrop, the X, Cancel or $dispatch('close-modal', name).
 */
Alpine.data('modal', (config) => ({
    open: false,
    returnTo: null,

    init() {
        window.addEventListener('open-modal', (event) => {
            const detail = typeof event.detail === 'string' ? { name: event.detail } : (event.detail ?? {});
            if (detail.name === config.name) this.show(detail.fill);
        });
        window.addEventListener('close-modal', (event) => (!event.detail || event.detail === config.name) && this.hide());
        if (config.open) this.$nextTick(() => this.show()); // reopened after a failed save
    },
    show(fill) {
        if (this.open) return;
        this.returnTo = document.activeElement;
        this.open = true;
        window.lockScroll(true);
        this.$nextTick(() => {
            if (fill) this.fill(fill);
            // once the panel is showing: the first field with an error, then [autofocus], then the first field
            setTimeout(() => {
                const body = this.$refs.body;
                const visible = (el) => el && el.offsetParent !== null;
                const errorField = body.querySelector('[data-field-error]')?.parentElement;
                const candidates = [
                    ...(errorField ? errorField.querySelectorAll(focusableSelector) : []),
                    body.querySelector('[autofocus]'),
                    ...body.querySelectorAll(focusableSelector),
                ];
                candidates.find(visible)?.focus({ preventScroll: true });
            }, 60);
        });
    },
    hide() {
        if (!this.open) return;
        this.open = false;
        window.lockScroll(false);
        this.returnTo?.focus?.({ preventScroll: true });
    },
    // set fields by name; change events let custom selects and pickers update themselves
    fill(values) {
        Object.entries(values).forEach(([name, value]) => {
            this.$refs.panel.querySelectorAll(`[name="${name}"]`).forEach((field) => {
                field.value = value ?? '';
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    },
    trap(event) {
        window.trapFocus(this.$refs.panel, event);
    },
}));

Alpine.store('confirm', {
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Confirm',
    tone: 'danger',
    resolve: null,

    ask({ title = 'Are you sure?', message = '', confirmLabel = 'Confirm', tone = 'danger' } = {}) {
        this.resolve?.(false);
        if (!this.open) window.lockScroll(true);
        Object.assign(this, { title, message, confirmLabel, tone: tone === 'primary' ? 'primary' : 'danger', open: true });
        return new Promise((resolve) => (this.resolve = resolve));
    },
    answer(value) {
        if (this.open) window.lockScroll(false);
        this.open = false;
        this.resolve?.(value);
        this.resolve = null;
    },
});
window.confirmAction = (options) => Alpine.store('confirm').ask(options);

document.addEventListener(
    'click',
    async (event) => {
        const trigger = event.target.closest('[data-confirm]');
        if (!trigger || trigger.dataset.confirmed === 'true' || trigger.disabled) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        const ok = await window.confirmAction({
            title: trigger.dataset.confirmTitle || 'Are you sure?',
            message: trigger.dataset.confirm,
            confirmLabel: trigger.dataset.confirmLabel || 'Confirm',
            tone: trigger.dataset.confirmTone || 'danger',
        });
        if (!ok) return trigger.focus?.();
        if (trigger.form && trigger.type === 'submit') {
            trigger.form.requestSubmit(trigger);
        } else if (trigger.href) {
            window.location.href = trigger.href;
        } else {
            trigger.dataset.confirmed = 'true';
            trigger.click();
            delete trigger.dataset.confirmed;
        }
    },
    true,
);

/**
 * Floating dropdown menu (<x-dropdown>): the panel is teleported to <body> and positioned next to
 * its trigger, so it is never clipped by a scrolling table or card.
 */
Alpine.data('dropdown', (config = {}) => ({
    open: false,
    panelStyle: '',
    align: config.align === 'left' ? 'left' : 'right',
    width: config.width ?? 192,

    init() {
        const reposition = () => this.open && this.place();
        window.addEventListener('scroll', reposition, true);
        window.addEventListener('resize', reposition);
    },
    toggle() {
        this.open ? this.close() : this.show();
    },
    show() {
        this.open = true;
        this.place();
        this.$nextTick(() => this.items()[0]?.focus({ preventScroll: true }));
    },
    close(refocus = false) {
        this.open = false;
        if (refocus) this.$refs.trigger.querySelector('button, a')?.focus();
    },
    place() {
        const rect = this.$refs.trigger.getBoundingClientRect();
        const left = this.align === 'right' ? rect.right - this.width : rect.left;
        const clamped = Math.max(8, Math.min(left, window.innerWidth - this.width - 8));
        const below = window.innerHeight - rect.bottom;
        const vertical = below < 260 && rect.top > below ? `bottom:${window.innerHeight - rect.top + 6}px` : `top:${rect.bottom + 6}px`;
        this.panelStyle = `position:fixed;left:${clamped}px;width:${this.width}px;${vertical}`;
    },
    items() {
        return [...(this.$refs.panel?.querySelectorAll('[role=menuitem]:not([disabled])') ?? [])];
    },
    onKey(event) {
        const items = this.items();
        const index = items.indexOf(document.activeElement);
        if (event.key === 'ArrowDown') items[(index + 1) % items.length]?.focus();
        else if (event.key === 'ArrowUp') items[(index - 1 + items.length) % items.length]?.focus();
        else if (event.key === 'Escape') this.close(true);
        else if (event.key === 'Tab') this.close();
        else return;
        if (event.key !== 'Tab') event.preventDefault();
    },
}));

/**
 * Tooltips: any element with data-tooltip="text" shows a small dark label on hover or keyboard
 * focus. One shared, fixed-position element is used, so tooltips are never clipped by tables.
 */
const tooltip = document.createElement('div');
tooltip.setAttribute('role', 'tooltip');
tooltip.className = 'app-tooltip';
let tooltipTarget = null;

function showTooltip(target) {
    const text = target.dataset.tooltip;
    if (!text) return;
    tooltipTarget = target;
    if (!tooltip.isConnected) document.body.append(tooltip);
    tooltip.textContent = text;
    tooltip.dataset.show = 'true';
    const rect = target.getBoundingClientRect();
    const box = tooltip.getBoundingClientRect();
    const above = rect.top - box.height - 8 > 0;
    tooltip.dataset.side = above ? 'top' : 'bottom';
    tooltip.style.left = `${Math.max(6, Math.min(rect.left + rect.width / 2 - box.width / 2, window.innerWidth - box.width - 6))}px`;
    tooltip.style.top = `${above ? rect.top - box.height - 8 : rect.bottom + 8}px`;
}
function hideTooltip() {
    tooltipTarget = null;
    delete tooltip.dataset.show;
}
document.addEventListener('pointerover', (event) => {
    const target = event.target.closest?.('[data-tooltip]');
    if (target && target !== tooltipTarget) showTooltip(target);
    else if (!target && tooltipTarget) hideTooltip();
});
document.addEventListener('focusin', (event) => {
    const target = event.target.closest?.('[data-tooltip]');
    target?.matches(':focus-visible') ? showTooltip(target) : hideTooltip();
});
document.addEventListener('focusout', hideTooltip);
document.addEventListener('pointerdown', hideTooltip);
window.addEventListener('scroll', hideTooltip, true);

/**
 * Submitting a form disables its submit button and shows a spinner, so a slow request can't be
 * sent twice. GET filter forms and forms marked data-no-loading are left alone.
 */
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (event.defaultPrevented || form.method.toLowerCase() === 'get' || form.hasAttribute('data-no-loading') || form.target) return;
    const button = event.submitter ?? form.querySelector('[type=submit]');
    if (!button) return;
    // let the browser read the submitter's name/value before it is disabled
    setTimeout(() => {
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
    });
});
// coming "back" to a page from the browser cache: re-enable its buttons
window.addEventListener('pageshow', (event) => {
    if (!event.persisted) return;
    document.querySelectorAll('[aria-busy="true"]').forEach((button) => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
    });
});

/*
 * Top bar search (resources/views/components/topbar.blade.php): pages the user can open are matched
 * here, records (students, teachers, users) come from the search route, both listed in one keyboard list.
 */
Alpine.data('commandPalette', (config) => ({
    open: false,
    query: '',
    records: [],
    loading: false,
    active: 0,
    icons: {},
    shortcut: /Mac|iPhone|iPad/.test(navigator.platform) ? '⌘K' : 'Ctrl K',
    request: null,

    init() {
        this.$refs.icons.querySelectorAll('[data-icon]').forEach((el) => (this.icons[el.dataset.icon] = el.innerHTML));
        this.$watch('query', () => (this.active = 0));
    },
    openPalette() {
        if (!this.open) window.lockScroll(true);
        this.open = true;
        this.$nextTick(() => this.$refs.input.select());
    },
    closePalette() {
        if (this.open) window.lockScroll(false);
        this.open = false;
    },
    get pageResults() {
        const words = this.query.toLowerCase().split(/\s+/).filter(Boolean);
        const pages = config.pages.filter((page) => {
            const text = `${page.section ?? ''} ${page.title}`.toLowerCase();
            return words.every((word) => text.includes(word));
        });
        return (words.length ? pages.slice(0, 6) : pages.slice(0, 8)).map((page) => ({ ...page, meta: page.section }));
    },
    // one flat, numbered list across groups so arrow keys move straight through
    get groups() {
        let index = 0;
        const number = (items) => items.map((item) => ({ ...item, index: index++ }));
        const groups = [];
        if (this.pageResults.length) groups.push({ label: this.query.trim() ? 'Pages' : 'Go to', items: number(this.pageResults) });
        this.records.forEach((group) => groups.push({ label: group.label, items: number(group.items) }));
        return groups;
    },
    get count() {
        return this.groups.reduce((total, group) => total + group.items.length, 0);
    },
    async fetchRecords() {
        const q = this.query.trim();
        this.request?.abort();
        if (q.length < 2) {
            this.records = [];
            this.loading = false;
            return;
        }
        this.request = new AbortController();
        this.loading = true;
        try {
            const response = await fetch(`${config.endpoint}?q=${encodeURIComponent(q)}`, {
                headers: { Accept: 'application/json' },
                signal: this.request.signal,
            });
            if (response.ok) this.records = (await response.json()).groups;
            this.loading = false;
        } catch (error) {
            if (error.name !== 'AbortError') this.loading = false;
        }
    },
    onKey(event) {
        if (event.key === 'Tab') {
            window.trapFocus(event.currentTarget, event);
        } else if (event.key === 'Escape') {
            event.preventDefault();
            this.closePalette();
        } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            if (!this.count) return;
            this.active = (this.active + (event.key === 'ArrowDown' ? 1 : -1) + this.count) % this.count;
            this.$nextTick(() => this.$refs.list.querySelector(`[data-index="${this.active}"]`)?.scrollIntoView({ block: 'nearest' }));
        } else if (event.key === 'Enter') {
            const link = this.$refs.list.querySelector(`[data-index="${this.active}"]`);
            if (link) {
                event.preventDefault();
                window.location.href = link.href;
            }
        }
    },
}));

/* Red dot on the notices bell until the newest notice has been seen in this browser. */
Alpine.data('noticeBell', (latest) => ({
    unread: false,
    init() {
        let seen = null;
        try {
            seen = localStorage.getItem('notices-seen');
        } catch {}
        this.unread = Boolean(latest) && (!seen || new Date(latest) > new Date(seen));
    },
    markSeen() {
        this.unread = false;
        try {
            if (latest) localStorage.setItem('notices-seen', latest);
        } catch {}
    },
}));

window.Alpine = Alpine;
Alpine.start();
