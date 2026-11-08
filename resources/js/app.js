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

window.Alpine = Alpine;
Alpine.start();
