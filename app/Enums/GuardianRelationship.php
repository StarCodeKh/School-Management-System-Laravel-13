<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum GuardianRelationship: string
{
    use HasOptions;

    case Father = 'father';
    case Mother = 'mother';
    case Guardian = 'guardian';
    case Other = 'other';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
