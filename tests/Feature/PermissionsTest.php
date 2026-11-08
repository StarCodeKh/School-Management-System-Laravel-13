<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    public static function adminOnlyPages(): array
    {
        return array_map(fn ($uri) => [$uri], [
            'student/add/page', 'teacher/add/page', 'department/add/page', 'subject/add/page',
            'class/list/page', 'list/users', 'setting/page',
        ]);
    }

    #[DataProvider('adminOnlyPages')]
    public function test_teachers_cannot_open_admin_pages(string $uri): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Teacher]))->get('/'.$uri)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => Role::Admin]))->get('/'.$uri)->assertOk();
    }

    public function test_teachers_can_view_but_not_change_students(): void
    {
        $teacher = User::factory()->create(['role' => Role::Teacher]);
        $student = Student::factory()->create();

        $this->actingAs($teacher)->get('/student/list')->assertOk()->assertSee($student->full_name)->assertDontSee('Add student');
        $this->actingAs($teacher)->get('/student/profile/'.$student->id)->assertOk();
        $this->actingAs($teacher)->withoutMiddleware(ValidateCsrfToken::class)->post('/student/delete', ['id' => $student->id])->assertForbidden();
        $this->assertModelExists($student);
    }

    public function test_students_see_only_their_own_dashboard(): void
    {
        $student = User::factory()->create(['role' => Role::Student]);

        foreach (['/student/list', '/teacher/list/page', '/department/list/page', '/subject/list/page', '/teacher/dashboard', '/attendance', '/finance'] as $uri) {
            $this->actingAs($student)->get($uri)->assertForbidden();
        }
        $this->actingAs($student)->get('/home')->assertRedirect('/student/dashboard');
        $this->actingAs($student)->get('/student/dashboard')->assertOk()->assertDontSee('Departments');
    }

    public function test_users_without_a_dashboard_land_on_their_profile(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Client]))->get('/home')->assertRedirect('/user/profile/page');
    }
}
