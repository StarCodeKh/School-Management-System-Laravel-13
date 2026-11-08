<?php

namespace App\Enums\Concerns;

/** For backed enums with a label() method: [value => label] for <select> options. */
trait HasOptions
{
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }
}
