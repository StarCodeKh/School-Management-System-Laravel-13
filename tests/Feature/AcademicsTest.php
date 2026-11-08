<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\Grade;
use App\Enums\Role;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacherUser;

    private Section $section;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::SuperAdmin]);
        $this->teacherUser = User::factory()->create(['role' => Role::Teacher]);
        $this->section = Section::factory()->create(['name' => 'A']);
    }

    private function student(array $attributes = []): Student
    {
        return Student::factory()->create($attributes + ['section_id' => $this->section->id]);
    }

    private function teacher(User $user): Teacher
    {
        return Teacher::create([
            'user_id' => $user->id, 'gender' => 'male', 'date_of_birth' => '1985-01-01', 'qualification' => 'BEd', 'experience' => 5,
            'phone_number' => '012', 'address' => 'St 1', 'city' => 'PP', 'state' => 'PP', 'zip_code' => '12000', 'country' => 'Cambodia',
        ]);
    }

    public function test_teacher_takes_attendance_for_a_section(): void
    {
        [$a, $b] = [$this->student(), $this->student()];
        $outsider = Student::factory()->create(); // other section

        $this->actingAs($this->teacherUser)->post('/attendance', [
            'section_id' => $this->section->id, 'date' => today()->toDateString(),
            'attendance' => [
                $a->id => ['status' => 'present'],
                $b->id => ['status' => 'absent', 'remark' => 'Sick'],
                $outsider->id => ['status' => 'present'],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, Attendance::count());
        $this->assertSame(AttendanceStatus::Absent, $b->attendances()->first()->status);
        $this->assertSame(0, $outsider->attendances()->count());

        // saving again updates instead of duplicating
        $this->actingAs($this->teacherUser)->post('/attendance', [
            'section_id' => $this->section->id, 'date' => today()->toDateString(),
            'attendance' => [$b->id => ['status' => 'late']],
        ]);
        $this->assertSame(2, Attendance::count());
        $this->assertSame(50.0, $a->attendanceRate() / 2);
        $this->assertSame(100.0, $b->fresh()->attendanceRate());

        $this->actingAs($this->teacherUser)->get('/attendance?section_id='.$this->section->id)->assertOk()->assertSee($a->full_name);
        $this->actingAs($this->teacherUser)->get('/attendance/report?section_id='.$this->section->id)->assertOk()->assertSee('100%');
    }

    public function test_attendance_cannot_be_recorded_for_the_future_or_by_students(): void
    {
        $student = $this->student();
        $payload = ['section_id' => $this->section->id, 'attendance' => [$student->id => ['status' => 'present']]];

        $this->actingAs($this->teacherUser)->post('/attendance', $payload + ['date' => today()->addDay()->toDateString()])->assertSessionHasErrors('date');
        $this->actingAs(User::factory()->create(['role' => Role::Student]))->post('/attendance', $payload + ['date' => today()->toDateString()])->assertForbidden();
    }

    public function test_exam_marks_produce_grades_and_ranks(): void
    {
        $math = Subject::create(['subject_name' => 'Math', 'school_class_id' => $this->section->school_class_id]);
        $khmer = Subject::create(['subject_name' => 'Khmer', 'school_class_id' => $this->section->school_class_id]);
        [$top, $second, $tied] = [$this->student(), $this->student(), $this->student()];

        $this->actingAs($this->admin)->post('/exams', [
            'school_class_id' => $this->section->school_class_id, 'name' => 'Midterm', 'start_date' => '2026-03-01', 'max_marks' => 100,
        ])->assertSessionHasNoErrors();
        $exam = Exam::firstOrFail();

        $this->actingAs($this->teacherUser)->post("/exams/{$exam->id}/marks", ['subject_id' => $math->id, 'marks' => [$top->id => 95, $second->id => 70, $tied->id => 70]])->assertSessionHasNoErrors();
        $this->actingAs($this->teacherUser)->post("/exams/{$exam->id}/marks", ['subject_id' => $khmer->id, 'marks' => [$top->id => 91, $second->id => 60, $tied->id => 60]])->assertSessionHasNoErrors();

        $sheet = $exam->resultSheet();
        $this->assertSame([1, 2, 2], $sheet->pluck('rank')->all());
        $this->assertSame($top->id, $sheet[0]['student']->id);
        $this->assertSame(93.0, $sheet[0]['percentage']);
        $this->assertSame(Grade::APlus, $sheet[0]['grade']);
        $this->assertSame(Grade::C, $sheet[1]['grade']);

        $this->actingAs($this->teacherUser)->get("/exams/{$exam->id}")->assertOk()->assertSee('A+');
        $this->actingAs($this->admin)->get("/exams/{$exam->id}/report-card/{$top->id}")->assertOk()->assertSee('1 of 3');
    }

    public function test_marks_above_maximum_or_from_other_classes_are_rejected(): void
    {
        $math = Subject::create(['subject_name' => 'Math', 'school_class_id' => $this->section->school_class_id]);
        $foreignSubject = Subject::create(['subject_name' => 'Art', 'school_class_id' => Section::factory()->create()->school_class_id]);
        $exam = Exam::create(['school_class_id' => $this->section->school_class_id, 'name' => 'Final', 'start_date' => '2026-06-01', 'max_marks' => 50]);
        $student = $this->student();

        $this->actingAs($this->teacherUser)->post("/exams/{$exam->id}/marks", ['subject_id' => $math->id, 'marks' => [$student->id => 60]])->assertSessionHasErrors("marks.{$student->id}");
        $this->actingAs($this->teacherUser)->post("/exams/{$exam->id}/marks", ['subject_id' => $foreignSubject->id, 'marks' => [$student->id => 10]])->assertSessionHasErrors('subject_id');
        $this->assertSame(0, ExamResult::count());
    }

    public function test_only_admins_create_exams_and_students_see_only_their_own_report_card(): void
    {
        $this->actingAs($this->teacherUser)->get('/exams/create')->assertForbidden();
        $this->actingAs($this->admin)->get('/exams/create')->assertOk();

        $exam = Exam::create(['school_class_id' => $this->section->school_class_id, 'name' => 'Quiz', 'start_date' => '2026-02-01']);
        $account = User::factory()->create(['role' => Role::Student]);
        $own = $this->student(['user_id' => $account->id]);
        $other = $this->student();

        $this->actingAs($account)->get("/exams/{$exam->id}/report-card/{$own->id}")->assertOk();
        $this->actingAs($account)->get("/exams/{$exam->id}/report-card/{$other->id}")->assertForbidden();
        $this->actingAs($account)->get('/exams')->assertForbidden();
    }

    public function test_timetable_rejects_double_booking_a_teacher(): void
    {
        $teacher = $this->teacher($this->teacherUser);
        $math = Subject::create(['subject_name' => 'Math', 'school_class_id' => $this->section->school_class_id]);
        $otherSection = Section::create(['school_class_id' => $this->section->school_class_id, 'name' => 'B']);
        $slot = ['day_of_week' => 1, 'period' => 1, 'start_time' => '08:00', 'end_time' => '08:45', 'subject_id' => $math->id, 'teacher_id' => $teacher->id];

        $this->actingAs($this->admin)->post('/timetable', $slot + ['section_id' => $this->section->id])->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post('/timetable', $slot + ['section_id' => $otherSection->id])->assertSessionHasErrors('teacher_id');
        $this->actingAs($this->admin)->post('/timetable', $slot + ['section_id' => $this->section->id, 'room' => 'B-1'])->assertSessionHasNoErrors();

        $this->assertSame(1, TimetableEntry::count());
        $this->assertSame('B-1', TimetableEntry::first()->room);
        $this->actingAs($this->teacherUser)->post('/timetable', $slot + ['section_id' => $this->section->id])->assertForbidden();
        $this->actingAs($this->teacherUser)->get('/timetable?section_id='.$this->section->id)->assertOk()->assertSee('Math');
    }
}
