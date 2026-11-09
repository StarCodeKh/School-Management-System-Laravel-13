<?php

namespace Database\Seeders\Demo;

use App\Enums\AttendanceStatus;
use App\Enums\Weekday;
use App\Models\Department;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Subjects for every class, a weekly timetable per section (no teacher booked twice in one
 * period), daily attendance for the last few months, and exams with marks.
 */
class AcademicsSeeder extends Seeder
{
    /** subject => department that teaches it */
    public const SUBJECTS = [
        'Khmer Literature' => 'Languages',
        'Mathematics' => 'Mathematics',
        'English' => 'Languages',
        'Physics' => 'Sciences',
        'Chemistry' => 'Sciences',
        'Biology' => 'Sciences',
        'History' => 'Social Studies',
        'Geography' => 'Social Studies',
        'ICT' => 'Information Technology',
        'Physical Education' => 'Sports & Arts',
    ];

    private const PERIODS = [
        1 => ['07:00', '07:50'], 2 => ['07:55', '08:45'], 3 => ['08:50', '09:40'],
        4 => ['10:00', '10:50'], 5 => ['10:55', '11:45'], 6 => ['13:30', '14:20'],
    ];

    /** student id => ability between 0 and 1, shared by attendance and marks */
    private array $ability = [];

    public function run(): void
    {
        $this->subjects();
        $this->timetables();
        $this->attendance();
        $this->exams();
    }

    private function subjects(): void
    {
        foreach (SchoolClass::all() as $class) {
            foreach (array_keys(self::SUBJECTS) as $name) {
                Subject::firstOrCreate(['school_class_id' => $class->id, 'subject_name' => $name]);
            }
        }
    }

    private function timetables(): void
    {
        $teachersByDepartment = Teacher::with('department')->get()->groupBy(fn (Teacher $teacher) => $teacher->department?->department_name);
        $busy = []; // "teacher-day-period" => true
        $load = []; // teacher id => lessons per week, to share work evenly
        $rows = [];

        foreach (Section::with('schoolClass.subjects')->get() as $section) {
            $subjects = $section->schoolClass->subjects->keyBy('subject_name');
            // 5 days x 6 periods = 30 lessons: three of each subject
            $lessons = DemoData::shuffle(collect(array_keys(self::SUBJECTS))->flatMap(fn ($name) => [$name, $name, $name]))->all();
            $teacherFor = []; // each subject keeps the same teacher all week when possible

            foreach (Weekday::cases() as $day) {
                if ($day === Weekday::Saturday) {
                    continue;
                }
                foreach (self::PERIODS as $period => [$start, $end]) {
                    $free = fn ($id) => $id && ! isset($busy["{$id}-{$day->value}-{$period}"]);
                    $teacherOf = function (string $name) use ($teachersByDepartment, $teacherFor, $free, &$load) {
                        if ($free($teacherFor[$name] ?? null)) {
                            return $teacherFor[$name];
                        }

                        // otherwise the least busy free teacher of the department
                        return ($teachersByDepartment[self::SUBJECTS[$name]] ?? collect())->pluck('id')
                            ->filter($free)->sortBy(fn ($id) => [$load[$id] ?? 0, $id])->first();
                    };

                    // take the first remaining lesson that has a free teacher in this period
                    $index = collect($lessons)->search(fn (string $name) => $teacherOf($name) !== null);
                    $index = $index === false ? array_key_first($lessons) : $index;
                    $name = $lessons[$index];
                    unset($lessons[$index]);

                    $teacherId = $teacherOf($name);
                    if ($teacherId) {
                        $busy["{$teacherId}-{$day->value}-{$period}"] = true;
                        $load[$teacherId] = ($load[$teacherId] ?? 0) + 1;
                        $teacherFor[$name] ??= $teacherId;
                    }

                    $rows[] = [
                        'section_id' => $section->id,
                        'day_of_week' => $day->value,
                        'period' => $period,
                        'start_time' => $start,
                        'end_time' => $end,
                        'subject_id' => $subjects[$name]->id,
                        'teacher_id' => $teacherId,
                        'room' => $name === 'Physical Education' ? 'Sports field' : ($name === 'ICT' ? 'Computer lab' : 'Room '.$section->schoolClass->name.$section->name),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('timetable_entries')->insert($chunk);
        }
    }

    private function attendance(): void
    {
        $faker = DemoData::faker();
        $days = DemoData::schoolDays(config('school.demo.months'));
        $recorders = Teacher::pluck('user_id');
        $remarks = [
            AttendanceStatus::Absent->value => ['Sick', 'No reason given', 'Family emergency', null],
            AttendanceStatus::Late->value => ['Traffic', 'Missed the bus', null],
            AttendanceStatus::Excused->value => ['Medical appointment', 'School competition', 'Family event'],
        ];

        $rows = [];
        foreach (Student::orderBy('id')->get(['id']) as $student) {
            $ability = $this->ability($student->id);
            // stronger students tend to miss fewer days
            $absent = (int) round(9 - 7 * $ability);
            foreach ($days as $date) {
                $status = DemoData::weighted([
                    AttendanceStatus::Present->value => 100 - $absent - 4,
                    AttendanceStatus::Absent->value => $absent,
                    AttendanceStatus::Late->value => 3,
                    AttendanceStatus::Excused->value => 1,
                ]);
                $rows[] = [
                    'student_id' => $student->id,
                    'date' => $date,
                    'status' => $status,
                    'remark' => isset($remarks[$status]) ? $faker->randomElement($remarks[$status]) : null,
                    'recorded_by' => $recorders->isEmpty() ? null : $faker->randomElement($recorders->all()),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (count($rows) >= 1000) {
                    DB::table('attendances')->insert($rows);
                    $rows = [];
                }
            }
        }
        if ($rows) {
            DB::table('attendances')->insert($rows);
        }
    }

    private function exams(): void
    {
        $plan = [
            // name, term, starts (days from today), length in days, max marks, marked?
            ['Monthly Test', 'Term 1', -45, 2, 50, true],
            ['Mid-term Examination', 'Term 1', -20, 5, 100, true],
            ['Final Examination', 'Term 2', 25, 6, 100, false],
        ];

        foreach (SchoolClass::with(['subjects', 'students:id,school_class_id'])->get() as $class) {
            foreach ($plan as [$name, $term, $starts, $length, $max, $marked]) {
                $exam = Exam::create([
                    'school_class_id' => $class->id,
                    'name' => $name,
                    'term' => $term,
                    'start_date' => today()->addDays($starts),
                    'end_date' => today()->addDays($starts + $length),
                    'max_marks' => $max,
                ]);
                if ($marked) {
                    $this->results($exam, $class->subjects, $class->students);
                }
            }
        }
    }

    private function results(Exam $exam, Collection $subjects, Collection $students): void
    {
        $faker = DemoData::faker();
        $rows = [];
        foreach ($students as $student) {
            $ability = $this->ability($student->id);
            foreach ($subjects as $subject) {
                // marks follow the student's ability, with some spread per subject
                $score = $this->normal($ability * 0.95, 0.1);
                $marks = round(max(0, min(1, $score)) * $exam->max_marks * 2) / 2;
                $rows[] = [
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'marks' => $marks,
                    'remark' => match (true) {
                        $score >= 0.9 => $faker->randomElement(['Excellent work', 'Outstanding', null]),
                        $score < 0.45 => $faker->randomElement(['Needs extra practice', 'Please see the teacher', null]),
                        default => null,
                    },
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        foreach (array_chunk($rows, 1000) as $chunk) {
            DB::table('exam_results')->insert($chunk);
        }
    }

    private function ability(int $studentId): float
    {
        return $this->ability[$studentId] ??= max(0.3, min(1, $this->normal(0.72, 0.14)));
    }

    /** A normally distributed number from the seeded Faker (Box-Muller). */
    private function normal(float $mean, float $deviation): float
    {
        $faker = DemoData::faker();
        $u = max(1e-9, $faker->randomFloat(9, 0, 1));
        $v = $faker->randomFloat(9, 0, 1);

        return $mean + $deviation * sqrt(-2 * log($u)) * cos(2 * M_PI * $v);
    }
}
