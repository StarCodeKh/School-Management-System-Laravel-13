<?php

namespace App\Models;

use App\Enums\Audience;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'location',
        'starts_on',
        'ends_on',
        'audience',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'audience' => Audience::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeVisibleTo(Builder $query, User $user): void
    {
        $query->whereIn('audience', Audience::for($user));
    }

    /** Events still running or starting from the given day. */
    public function scopeUpcoming(Builder $query): void
    {
        $query->where(fn ($q) => $q->whereDate('starts_on', '>=', today())->orWhereDate('ends_on', '>=', today()))->orderBy('starts_on');
    }

    public function endsOn(): Carbon
    {
        return $this->ends_on ?? $this->starts_on;
    }

    /** "12 Nov" or "12 - 14 Nov 2026" */
    public function dateRange(): string
    {
        if (! $this->ends_on || $this->ends_on->isSameDay($this->starts_on)) {
            return $this->starts_on->format('D d M Y');
        }

        return $this->starts_on->format('d M').' - '.$this->ends_on->format('d M Y');
    }
}
