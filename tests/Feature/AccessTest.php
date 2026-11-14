<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    public static function protectedPostRoutes(): array
    {
        return [
            ['student/add/save'], ['student/update'], ['student/delete'],
            ['subject/save'], ['subject/update'], ['subject/delete'],
            ['teacher/delete'],
            ['invoice/add/save'], ['invoice/update/save'], ['invoice/delete'],
            ['change/password'],
        ];
    }

    #[DataProvider('protectedPostRoutes')]
    public function test_guests_cannot_change_data(string $uri): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post('/'.$uri)
            ->assertRedirect('/login');
    }

    public function test_guest_cannot_delete_a_student(): void
    {
        $student = Student::factory()->create(['first_name' => 'Sok', 'last_name' => 'Dara']);

        $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post('/student/delete', ['id' => $student->id]);

        $this->assertModelExists($student);
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/change/password', [
            'current_password' => 'password',
            'new_password' => 'NewPassword@123',
            'new_confirm_password' => 'NewPassword@123',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('NewPassword@123', $user->fresh()->password));
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/change/password', [
            'current_password' => 'wrong-password',
            'new_password' => 'NewPassword@123',
            'new_confirm_password' => 'NewPassword@123',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_sign_up_is_closed_by_default(): void
    {
        $this->get('/login')->assertOk()->assertDontSee('Sign up');
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Visitor',
            'email' => 'visitor@example.com',
            'role' => Role::registrable()[0]->value,
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'visitor@example.com']);
    }

    public function test_admin_can_open_sign_up_from_settings(): void
    {
        $admin = User::factory()->create(['role' => Role::SuperAdmin]);
        $this->actingAs($admin)->post('/setting/general', [
            'school_name' => 'Test School',
            'currency_symbol' => '$',
            'allow_registration' => '1',
        ])->assertSessionHasNoErrors();
        auth()->logout();

        $this->get('/login')->assertSee('Sign up');
        $this->get('/register')->assertOk();
    }

    public function test_visitor_cannot_register_as_admin(): void
    {
        Setting::store(['allow_registration' => '1']);

        $this->post('/register', [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'role' => Role::SuperAdmin->value,
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'intruder@example.com']);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass', 'status' => UserStatus::Inactive]);

        $this->post('/login', ['email' => $user->email, 'password' => 'secret-pass'])
            ->assertRedirect()->assertSessionHas('error', 'Your account is inactive. Please contact the school office.');
        $this->assertGuest();
    }

    public function test_user_deactivated_while_logged_in_is_signed_out(): void
    {
        $user = User::factory()->create(['role' => Role::Teacher]);
        $this->actingAs($user)->get('/user/profile/page')->assertOk();

        $user->update(['status' => UserStatus::Inactive]);

        $this->actingAs($user)->get('/user/profile/page')->assertRedirect('/login');
        $this->assertGuest();
    }
}
