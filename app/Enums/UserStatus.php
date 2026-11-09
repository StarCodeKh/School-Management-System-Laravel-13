<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum UserStatus: string
{
    use HasOptions;

    case Active = 'active';
    case Inactive = 'inactive';
    case Disabled = 'disabled';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Badge colour (see <x-badge>). */
    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Inactive => 'amber',
            self::Disabled => 'red',
        };
    }

    /** What the status means for the person, shown when choosing one. */
    public function description(): string
    {
        return match ($this) {
            self::Active => 'Can log in and use the system.',
            self::Inactive => 'Cannot log in, e.g. waiting for approval or on leave.',
            self::Disabled => 'Cannot log in; the account is closed.',
        };
    }
}
