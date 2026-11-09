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

window.Alpine = Alpine;
Alpine.start();
