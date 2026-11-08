<?php

namespace App\Models;

use App\Models\Concerns\HasDisplayCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    use HasDisplayCode, HasFactory;

    public const DISPLAY_CODE_COLUMN = 'code';

    public const DISPLAY_CODE_PREFIX = 'SUB-';

    protected $fillable = [
        'subject_name',
        'school_class_id',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }
}
