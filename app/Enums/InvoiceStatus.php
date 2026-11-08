<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Bootstrap badge class used in the invoice list and grid. */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-default-light',
            self::Sent => 'bg-primary-light',
            self::Paid => 'bg-success-light',
            self::Cancelled => 'bg-danger-light',
        };
    }
}
