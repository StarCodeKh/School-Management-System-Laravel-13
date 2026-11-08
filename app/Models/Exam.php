<?php

namespace App\Models;

use App\Enums\Grade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Exam extends Model
{
    protected $fillable = [
        'school_class_id',
        'name',
        'term',
        'start_date',
        'end_date',
        'max_marks',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'max_marks' => 'integer',
        ];
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    /** Subjects taught in the exam's class. */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'school_class_id', 'school_class_id')->orderBy('subject_name');
    }

    /** Students of the exam's class. */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'school_class_id', 'school_class_id');
    }

    /**
     * One row per student of the class: marks per subject, total, percentage, grade and rank.
     * Ranks are shared on equal percentages (1, 2, 2, 4).
     *
     * @return Collection<int, array{student: Student, marks: array<int, ?float>, total: float, percentage: float, grade: Grade, rank: int, complete: bool}>
     */
    public function resultSheet(): Collection
    {
        $subjects = $this->subjects()->get();
        $results = $this->results()->get()->groupBy('student_id');
        $maxTotal = max(1, $subjects->count() * $this->max_marks);

        $rows = $this->students()->with(['schoolClass', 'section'])->orderBy('first_name')->get()
            ->map(function (Student $student) use ($subjects, $results, $maxTotal) {
                $byStudent = ($results[$student->id] ?? collect())->keyBy('subject_id');
                $marks = $subjects->mapWithKeys(fn (Subject $subject) => [$subject->id => isset($byStudent[$subject->id]) ? (float) $byStudent[$subject->id]->marks : null])->all();
                $total = array_sum(array_filter($marks, fn ($mark) => $mark !== null));
                $percentage = round($total / $maxTotal * 100, 1);

                return [
                    'student' => $student,
                    'marks' => $marks,
                    'total' => $total,
                    'percentage' => $percentage,
                    'grade' => Grade::fromPercentage($percentage),
                    'complete' => ! in_array(null, $marks, true),
                ];
            })
            ->sortByDesc('percentage')
            ->values();

        $rank = 0;
        $previous = null;

        return $rows->map(function (array $row, int $index) use (&$rank, &$previous) {
            if ($row['percentage'] !== $previous) {
                $rank = $index + 1;
                $previous = $row['percentage'];
            }

            return $row + ['rank' => $rank];
        });
    }
}
