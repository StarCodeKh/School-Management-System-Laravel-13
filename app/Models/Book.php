<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'category',
        'publisher',
        'shelf',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function loans(): HasMany
    {
        return $this->hasMany(BookLoan::class);
    }

    public function activeLoans(): HasMany
    {
        return $this->loans()->whereNull('returned_on');
    }

    /** Adds active_loans_count, used by available(). */
    public function scopeWithAvailability(Builder $query): void
    {
        $query->withCount('activeLoans');
    }

    public function available(): int
    {
        $onLoan = $this->active_loans_count ?? $this->activeLoans()->count();

        return max(0, $this->quantity - $onLoan);
    }
}
