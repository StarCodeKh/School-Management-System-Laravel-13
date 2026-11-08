<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/** ISO day numbers (Monday = 1), as stored in timetable_entries.day_of_week. */
enum Weekday: int
{
    use HasOptions;

    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;

    public function label(): string
    {
        return $this->name;
    }

    public static function today(): ?self
    {
        return self::tryFrom(now()->dayOfWeekIso);
    }
}
