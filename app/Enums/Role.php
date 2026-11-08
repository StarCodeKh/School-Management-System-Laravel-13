<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case NormalUser = 'normal_user';
    case Teacher = 'teacher';
    case Student = 'student';
    case Staff = 'staff';
    case Client = 'client';

    /** Display name shown in the UI. */
    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
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
}
