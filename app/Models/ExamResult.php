<?php

namespace App\Models;

use App\Enums\Grade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'subject_id',
        'marks',
        'remark',
    ];

    protected function casts(): array
    {
        return [
            'marks' => 'decimal:2',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function percentage(): float
    {
        return $this->exam->max_marks > 0 ? round($this->marks / $this->exam->max_marks * 100, 1) : 0;
    }

    public function grade(): Grade
    {
        return Grade::fromPercentage($this->percentage());
    }
}
