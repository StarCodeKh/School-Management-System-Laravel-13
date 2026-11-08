<?php

namespace App\Models;

use App\Models\Concerns\HasDisplayCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasDisplayCode, HasFactory;

    public const DISPLAY_CODE_COLUMN = 'code';

    public const DISPLAY_CODE_PREFIX = 'DEP-';

    protected $fillable = [
        'department_name',
        'head_user_id',
        'department_start_date',
        'no_of_students',
    ];

    protected function casts(): array
    {
        return [
            'department_start_date' => 'date',
            'no_of_students' => 'integer',
        ];
    }

    /** The head of department's user account. */
    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }
}
