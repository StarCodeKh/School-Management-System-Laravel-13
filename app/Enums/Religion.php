<?php

namespace App\Enums;

enum Religion: string
{
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
