<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum AttendanceStatus: string
{
    use HasOptions;

    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    case Excused = 'excused';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Badge color name for <x-badge>. */
    public function color(): string
    {
        return match ($this) {
            self::Present => 'green',
            self::Absent => 'red',
            self::Late => 'amber',
            self::Excused => 'blue',
        };
    }

    /** Counts toward attendance rate: present and late. */
    public function isAttending(): bool
    {
        return in_array($this, [self::Present, self::Late], true);
    }
}
