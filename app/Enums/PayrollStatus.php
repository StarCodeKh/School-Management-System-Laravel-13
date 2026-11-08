<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum PayrollStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Paid = 'paid';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return $this === self::Paid ? 'green' : 'amber';
    }
}
