<?php

namespace App\Models;

use App\Enums\BloodGroup;
use App\Enums\Gender;
use App\Enums\Religion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
