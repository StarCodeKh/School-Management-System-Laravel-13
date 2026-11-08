<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookLoan extends Model
{
    protected $fillable = [
        'book_id',
        'student_id',
        'issued_on',
        'due_on',
        'returned_on',
        'fine',
        'issued_by',
    ];

    protected function casts(): array
    {
        return [
            'issued_on' => 'date',
            'due_on' => 'date',
            'returned_on' => 'date',
            'fine' => 'decimal:2',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereNull('returned_on');
    }

    public function scopeOverdue(Builder $query): void
    {
        $query->whereNull('returned_on')->whereDate('due_on', '<', today());
    }

    public function isOverdue(): bool
    {
        return ! $this->returned_on && $this->due_on->isBefore(today());
    }

    public function daysLate(?\DateTimeInterface $on = null): int
    {
        $end = $this->returned_on ?? $on ?? today();

        return max(0, (int) $this->due_on->diffInDays($end, false));
    }

    /** Fine owed so far (or charged on return) at the configured daily rate. */
    public function currentFine(): float
    {
        return $this->returned_on ? (float) $this->fine : $this->daysLate() * (float) setting('library_fine_per_day', 0);
    }
}
