<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum ExpenseCategory: string
{
    use HasOptions;

    case Utilities = 'utilities';
    case Supplies = 'supplies';
    case Maintenance = 'maintenance';
    case Transport = 'transport';
    case Events = 'events';
    case Equipment = 'equipment';
    case Other = 'other';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
