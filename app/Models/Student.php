<?php

namespace App\Models;

use App\Enums\BloodGroup;
use App\Enums\Gender;
use App\Enums\Religion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admission_id',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'blood_group',
        'religion',
        'email',
        'phone_number',
        'school_class_id',
        'section_id',
        'roll',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'gender' => Gender::class,
            'blood_group' => BloodGroup::class,
            'religion' => Religion::class,
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /** Public URL of the photo, falling back to the default picture. */
    public function getPhotoUrlAttribute(): string
    {
        return asset($this->photo ?: 'images/photo_defaults.jpg');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function feeCollections(): HasMany
    {
        return $this->hasMany(FeeCollection::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    /** Share of recorded days the student attended (present or late), or null when nothing is recorded. */
    public function attendanceRate(): ?float
    {
        $statuses = $this->attendances()->pluck('status');

        if ($statuses->isEmpty()) {
            return null;
        }

        return round($statuses->filter(fn ($status) => $status->isAttending())->count() / $statuses->count() * 100, 1);
    }

    /**
     * Fees due for the student's class, paid so far and the remaining balance,
     * overall and per fee type.
     *
     * @return array{due: float, paid: float, balance: float, by_type: Collection}
     */
    public function feeSummary(): array
    {
        $structures = FeeStructure::with('feeType')->where('school_class_id', $this->school_class_id)->get();
        $paidByType = $this->feeCollections()->selectRaw('fee_type_id, SUM(amount) as total')->groupBy('fee_type_id')->pluck('total', 'fee_type_id');

        $byType = $structures->map(fn (FeeStructure $structure) => [
            'fee_type' => $structure->feeType,
            'due' => (float) $structure->amount,
            'paid' => (float) ($paidByType[$structure->fee_type_id] ?? 0),
            'due_date' => $structure->due_date,
        ]);
        $due = $byType->sum('due');
        $paid = (float) $paidByType->sum();

        return ['due' => $due, 'paid' => $paid, 'balance' => max(0, $due - $paid), 'by_type' => $byType];
    }

    /** Parent/guardian accounts linked to this student. */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guardian_student')->withPivot('relationship')->withTimestamps();
    }
}
