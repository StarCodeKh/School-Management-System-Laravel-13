<?php

namespace App\Models;

use App\Enums\Audience;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    protected $fillable = [
        'title',
        'body',
        'audience',
        'is_pinned',
        'published_at',
        'expires_on',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'audience' => Audience::class,
            'is_pinned' => 'boolean',
            'published_at' => 'datetime',
            'expires_on' => 'date',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Published, not expired, and meant for this user. */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        $query->whereIn('audience', Audience::for($user))
            ->where('published_at', '<=', now())
            ->where(fn ($q) => $q->whereNull('expires_on')->orWhereDate('expires_on', '>=', today()));
    }

    public function isActive(): bool
    {
        return $this->published_at->isPast() && (! $this->expires_on || ! $this->expires_on->isBefore(today()));
    }
}
