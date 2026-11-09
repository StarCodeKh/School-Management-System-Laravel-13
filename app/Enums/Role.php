<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum Role: string
{
    use HasOptions;

    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case NormalUser = 'normal_user';
    case Teacher = 'teacher';
    case Student = 'student';
    case Staff = 'staff';
    case Client = 'client';
    case Parent = 'parent';

    /** Display name shown in the UI. */
    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    /** Badge colour (see <x-badge>). */
    public function color(): string
    {
        return match ($this) {
            self::SuperAdmin, self::Admin => 'indigo',
            self::Teacher => 'blue',
            self::Student => 'green',
            self::Parent => 'amber',
            default => 'slate',
        };
    }

    /** Roles allowed to manage other users. */
    public function isAdmin(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin], true);
    }

    /**
     * Roles a visitor may choose on the public register page.
     *
     * @return array<int, self>
     */
    public static function registrable(): array
    {
        return array_values(array_filter(self::cases(), fn (self $role) => ! $role->isAdmin()));
    }

    /**
     * Roles that are school employees (can receive salary).
     *
     * @return array<int, self>
     */
    public static function staff(): array
    {
        return [self::SuperAdmin, self::Admin, self::Teacher, self::Staff];
    }
}
