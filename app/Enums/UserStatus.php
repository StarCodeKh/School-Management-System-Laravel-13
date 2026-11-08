<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Disabled = 'disabled';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Badge class used in the users table. */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'bg-success-dark',
            self::Inactive => 'badge-warning',
            self::Disabled => 'bg-danger-dark',
        };
    }
}
