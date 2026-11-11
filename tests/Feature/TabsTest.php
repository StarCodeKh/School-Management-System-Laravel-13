<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TabsTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_tabs_show_how_many_marks_are_entered(): void
    {
        $admin = User::factory()->create(['role' => Role::SuperAdmin]);
        $first = Student::factory()->create();
        $second = Student::factory()->create(['section_id' => $first->section_id]);

        $exam = Exam::create(['school_class_id' => $first->school_class_id, 'name' => 'Midterm', 'start_date' => today(), 'max_marks' => 100]);
        $maths = Subject::create(['school_class_id' => $first->school_class_id, 'subject_name' => 'Mathematics']);
        $physics = Subject::create(['school_class_id' => $first->school_class_id, 'subject_name' => 'Physics']);
        ExamResult::create(['exam_id' => $exam->id, 'student_id' => $first->id, 'subject_id' => $maths->id, 'marks' => 80]);
        foreach ([$first, $second] as $student) {
            ExamResult::create(['exam_id' => $exam->id, 'student_id' => $student->id, 'subject_id' => $physics->id, 'marks' => 70]);
        }

        $this->actingAs($admin)->get(route('exams.marks', [$exam, 'subject_id' => $maths->id]))->assertOk()
            ->assertSee('aria-current="page"', false)
            ->assertSeeInOrder(['Mathematics', '1/2', 'Physics', 'All 2 entered']);
    }

    public function test_layout_switch_marks_the_current_view(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::SuperAdmin]))->get('/student/grid')->assertOk()
            ->assertSeeInOrder(['aria-label="Layout"', 'List', 'aria-current="page"', 'Grid'], false);
    }
}
