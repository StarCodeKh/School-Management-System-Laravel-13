<?php

namespace App\Enums;

/** Letter grade from a percentage score. */
enum Grade: string
{
    case APlus = 'A+';
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case F = 'F';

    /** Lowest percentage for each grade, highest first. */
    private const THRESHOLDS = ['A+' => 90, 'A' => 80, 'B' => 70, 'C' => 60, 'D' => 50, 'F' => 0];

    public static function fromPercentage(float $percentage): self
    {
        foreach (self::THRESHOLDS as $grade => $minimum) {
            if ($percentage >= $minimum) {
                return self::from($grade);
            }
        }

        return self::F;
    }

    public function minimum(): int
    {
        return self::THRESHOLDS[$this->value];
    }

    public function passed(): bool
    {
        return $this !== self::F;
    }

    public function color(): string
    {
        return match ($this) {
            self::APlus, self::A => 'green',
            self::B, self::C => 'blue',
            self::D => 'amber',
            self::F => 'red',
        };
    }
}
