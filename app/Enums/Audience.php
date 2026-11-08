<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;
use App\Models\User;

/** Who a notice or event is for. Admins always see everything. */
enum Audience: string
{
    use HasOptions;

    case Everyone = 'everyone';
    case Staff = 'staff';
    case Teachers = 'teachers';
    case Students = 'students';
    case Parents = 'parents';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Everyone => 'indigo',
            self::Staff, self::Teachers => 'blue',
            self::Students => 'green',
            self::Parents => 'amber',
        };
    }

    /**
     * Audiences that include this user.
     *
     * @return array<int, self>
     */
    public static function for(User $user): array
    {
        if ($user->isAdmin()) {
            return self::cases();
        }

        return match ($user->role) {
            Role::Teacher => [self::Everyone, self::Staff, self::Teachers],
            Role::Staff => [self::Everyone, self::Staff],
            Role::Student => [self::Everyone, self::Students],
            Role::Parent => [self::Everyone, self::Parents],
            default => [self::Everyone],
        };
    }
}
