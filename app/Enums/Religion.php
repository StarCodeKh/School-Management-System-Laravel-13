<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum Religion: string
{
    use HasOptions;

    case Buddhism = 'buddhism';
    case Christianity = 'christianity';
    case Islam = 'islam';
    case Hinduism = 'hinduism';
    case Other = 'other';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
