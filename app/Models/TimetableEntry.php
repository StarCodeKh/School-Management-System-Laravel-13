<?php

namespace App\Models;

use App\Enums\Weekday;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableEntry extends Model
{
    protected $fillable = [
        'section_id',
        'day_of_week',
        'period',
        'start_time',
        'end_time',
        'subject_id',
        'teacher_id',
        'room',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => Weekday::class,
            'period' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /** "08:00 - 08:45" */
    public function timeRange(): string
    {
        return substr($this->start_time, 0, 5).' - '.substr($this->end_time, 0, 5);
    }
}
