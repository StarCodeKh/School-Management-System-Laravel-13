<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Models\Concerns\HasDisplayCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasDisplayCode, HasFactory;

    public const DISPLAY_CODE_COLUMN = 'invoice_number';

    public const DISPLAY_CODE_PREFIX = 'INV-';

    protected $fillable = [
        'customer_id',
        'customer_name',
        'po_number',
        'invoice_date',
        'due_date',
        'status',
        'enable_tax',
        'is_recurring',
        'recurring_by_month',
        'recurring_month',
        'invoice_from',
        'invoice_to',
        'taxable_amount',
        'round_off',
        'additional_charges',
        'discounts',
        'total_amount',
        'account_holder_name',
        'bank_name',
        'ifsc_code',
        'account_number',
        'terms_and_conditions',
        'notes',
        'signature_path',
        'signatory_name',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'invoice_date' => 'date',
            'due_date' => 'date',
            'enable_tax' => 'boolean',
            'is_recurring' => 'boolean',
            'taxable_amount' => 'decimal:2',
            'round_off' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'additional_charges' => 'array',
            'discounts' => 'array',
        ];
    }

    /** Route URLs use the invoice number, e.g. /invoice/view/INV-00001 */
    public function getRouteKeyName(): string
    {
        return 'invoice_number';
    }

    /** Unpaid, not cancelled, and past its due date. */
    public function isOverdue(): bool
    {
        return in_array($this->status, [InvoiceStatus::Draft, InvoiceStatus::Sent], true)
            && $this->due_date?->isPast() && ! $this->due_date->isToday();
    }

    /** Status shown to users: "Overdue" is derived from the due date, not stored. */
    public function statusLabel(): string
    {
        return $this->isOverdue() ? 'Overdue' : $this->status->label();
    }

    public function statusBadgeClass(): string
    {
        return $this->isOverdue() ? 'bg-warning-light' : $this->status->badgeClass();
    }

    public function scopeOverdue(Builder $query): void
    {
        $query->whereIn('status', [InvoiceStatus::Draft, InvoiceStatus::Sent])
            ->whereDate('due_date', '<', today());
    }

    /**
     * Narrow the list to one tab of the invoices page.
     * Tabs: all, paid, overdue, draft, recurring, cancelled.
     */
    public function scopeForTab(Builder $query, string $tab): void
    {
        match ($tab) {
            'paid' => $query->where('status', InvoiceStatus::Paid),
            'cancelled' => $query->where('status', InvoiceStatus::Cancelled),
            'draft' => $query->where('status', InvoiceStatus::Draft),
            'overdue' => $query->overdue(),
            'recurring' => $query->where('is_recurring', true),
            default => null,
        };
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
