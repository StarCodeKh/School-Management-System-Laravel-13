<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'gender',
        'date_of_birth',
        'qualification',
        'experience',
        'phone_number',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'experience' => 'integer',
            'gender' => Gender::class,
        ];
    }

    /** The teacher's name is stored once, on the linked user account. */
    public function getFullNameAttribute(): ?string
    {
        return $this->user?->name;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
