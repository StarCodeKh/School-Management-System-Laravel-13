<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Department;
use App\Models\FeeCollection;
use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SchemaRulesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::SuperAdmin]);
    }

    private function studentPayload(Section $section, array $overrides = []): array
    {
        return $overrides + [
            'first_name' => 'Sok', 'last_name' => 'Dara', 'gender' => 'male', 'date_of_birth' => '2010-01-01',
            'roll' => '7', 'blood_group' => 'O+', 'religion' => 'buddhism', 'email' => 'sok@example.com',
            'school_class_id' => $section->school_class_id, 'section_id' => $section->id,
            'admission_id' => 'ADM-9', 'phone_number' => '012345678',
            'photo' => UploadedFile::fake()->image('photo.jpg'),
        ];
    }

    public function test_display_codes_are_generated(): void
    {
        $subject = Subject::create(['subject_name' => 'Math', 'school_class_id' => SchoolClass::factory()->create()->id]);

        $this->assertSame('USR-'.str_pad((string) $this->admin->id, 5, '0', STR_PAD_LEFT), $this->admin->fresh()->code);
        $this->assertStringStartsWith('SUB-', $subject->fresh()->code);
    }

    public function test_student_is_saved_with_class_and_section_foreign_keys(): void
    {
        $section = Section::factory()->create();

        $this->actingAs($this->admin)->post('/student/add/save', $this->studentPayload($section))
            ->assertSessionHasNoErrors();

        $student = Student::firstOrFail();
        $this->assertTrue($student->section->is($section));
        $this->assertSame('male', $student->gender->value);
        @unlink(public_path($student->photo));
    }

    public function test_student_section_must_belong_to_the_chosen_class(): void
    {
        $section = Section::factory()->create();
        $otherClass = SchoolClass::factory()->create();

        $this->actingAs($this->admin)
            ->post('/student/add/save', $this->studentPayload($section, ['school_class_id' => $otherClass->id]))
            ->assertSessionHasErrors('section_id');
    }

    public function test_student_rejects_values_outside_the_enums(): void
    {
        $section = Section::factory()->create();

        $this->actingAs($this->admin)
            ->post('/student/add/save', $this->studentPayload($section, ['gender' => 'robot', 'blood_group' => 'Z+']))
            ->assertSessionHasErrors(['gender', 'blood_group']);
    }

    public function test_student_with_payments_cannot_be_deleted(): void
    {
        $student = Student::factory()->create();
        FeeCollection::create([
            'student_id' => $student->id, 'fee_type_id' => FeeType::create(['name' => 'Tuition'])->id,
            'amount' => 10, 'paid_date' => now(),
        ]);

        $this->actingAs($this->admin)->post('/student/delete', ['id' => $student->id])->assertSessionHas('error');

        $this->assertModelExists($student);
    }

    public function test_subject_name_is_unique_per_class_only(): void
    {
        [$ten, $eleven] = SchoolClass::factory()->count(2)->create();
        Subject::create(['subject_name' => 'Math', 'school_class_id' => $ten->id]);

        $this->actingAs($this->admin)->post('/subject/save', ['subject_name' => 'Math', 'school_class_id' => $ten->id])
            ->assertSessionHasErrors('subject_name');
        $this->actingAs($this->admin)->post('/subject/save', ['subject_name' => 'Math', 'school_class_id' => $eleven->id])
            ->assertSessionHasNoErrors();
    }

    public function test_class_is_created_with_sections_and_sections_sync_on_update(): void
    {
        $this->actingAs($this->admin)->post('/class/save', ['name' => '9', 'sections' => 'a, B ,b'])
            ->assertSessionHasNoErrors();

        $class = SchoolClass::where('name', '9')->firstOrFail();
        $this->assertSame(['A', 'B'], $class->sections->pluck('name')->all());

        $this->actingAs($this->admin)->post('/class/update/'.$class->id, ['name' => '9', 'sections' => 'B, C'])
            ->assertSessionHasNoErrors();
        $this->assertSame(['B', 'C'], $class->fresh()->sections->pluck('name')->all());
    }

    public function test_section_with_students_cannot_be_removed_and_class_cannot_be_deleted(): void
    {
        $student = Student::factory()->create();
        $class = $student->schoolClass;

        $this->actingAs($this->admin)->post('/class/update/'.$class->id, ['name' => $class->name, 'sections' => 'ZZ'])
            ->assertSessionHas('error');
        $this->assertModelExists($student->section);

        $this->actingAs($this->admin)->post('/class/delete', ['id' => $class->id])->assertSessionHas('error');
        $this->assertModelExists($class);
    }

    public function test_teacher_belongs_to_a_department_and_only_teachers_can_be_head(): void
    {
        $department = Department::create(['department_name' => 'Science', 'department_start_date' => '2020-01-01', 'no_of_students' => 0]);
        $teacherUser = User::factory()->create(['role' => Role::Teacher]);

        $this->actingAs($this->admin)->post('/teacher/save', [
            'user_id' => $teacherUser->id, 'department_id' => $department->id, 'gender' => 'female',
            'experience' => 3, 'date_of_birth' => '1990-01-01', 'qualification' => 'MSc', 'phone_number' => '012',
            'address' => 'St 1', 'city' => 'Phnom Penh', 'state' => 'PP', 'zip_code' => '12000', 'country' => 'Cambodia',
        ])->assertSessionHasNoErrors();

        $teacher = Teacher::where('user_id', $teacherUser->id)->firstOrFail();
        $this->assertTrue($teacher->department->is($department));
        $this->assertTrue($department->teachers->contains($teacher));

        // a user without a teacher profile is rejected as head of department
        $this->actingAs($this->admin)->post('/department/update', [
            'code' => $department->fresh()->code, 'department_name' => 'Science', 'head_user_id' => $this->admin->id,
            'department_start_date' => '2020-01-01', 'no_of_students' => 0,
        ])->assertSessionHasErrors('head_user_id');

        $this->actingAs($this->admin)->post('/department/update', [
            'code' => $department->fresh()->code, 'department_name' => 'Science', 'head_user_id' => $teacherUser->id,
            'department_start_date' => '2020-01-01', 'no_of_students' => 0,
        ])->assertSessionHasNoErrors();
        $this->assertTrue($department->fresh()->head->is($teacherUser));
    }
}
