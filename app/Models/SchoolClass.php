<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A grade level, e.g. "10". Named SchoolClass because "Class" is reserved in PHP. */
class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('name');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class)->latest('start_date');
    }

    /**
     * Sections grouped by class for <x-form.select>, e.g. ['Class 10' => [1 => '10 - A']].
     */
    public static function sectionOptions(): array
    {
        return static::with('sections')->orderBy('sort_order')->orderBy('name')->get()
            ->mapWithKeys(fn (self $class) => [
                'Class '.$class->name => $class->sections->mapWithKeys(fn (Section $section) => [$section->id => $class->name.' - '.$section->name])->all(),
            ])->all();
    }
}
