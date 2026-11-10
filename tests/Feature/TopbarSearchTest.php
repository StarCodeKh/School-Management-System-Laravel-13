<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Notice;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopbarSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_finds_students_and_users_by_any_word(): void
    {
        $admin = User::factory()->create(['role' => Role::SuperAdmin, 'name' => 'Sokha Admin']);
        Student::factory()->create(['first_name' => 'Dara', 'last_name' => 'Kim']);
        Student::factory()->create(['first_name' => 'Dara', 'last_name' => 'Sok']);

        $groups = collect($this->actingAs($admin)->getJson('/search?q=dara kim')->assertOk()->json('groups'))->keyBy('label');

        $this->assertSame(['Dara Kim'], array_column($groups['Students']['items'], 'title'));
        $this->assertArrayNotHasKey('Users', $groups->all()); // no user matches both words

        $this->assertSame('Sokha Admin', $this->getJson('/search?q=sokha')->json('groups.0.items.0.title'));
    }

    public function test_students_and_parents_get_no_records(): void
    {
        Student::factory()->create(['first_name' => 'Dara']);

        foreach ([Role::Student, Role::Parent] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->getJson('/search?q=dara')->assertOk()->assertExactJson(['groups' => []]);
        }
    }

    public function test_teachers_do_not_see_user_accounts(): void
    {
        User::factory()->create(['name' => 'Dara Account']);

        $labels = collect($this->actingAs(User::factory()->create(['role' => Role::Teacher]))->getJson('/search?q=dara')->json('groups'))->pluck('label');

        $this->assertNotContains('Users', $labels);
    }

    public function test_search_needs_a_login_and_two_characters(): void
    {
        $this->getJson('/search?q=da')->assertUnauthorized();
        $this->actingAs(User::factory()->create(['role' => Role::SuperAdmin]))->getJson('/search?q=d')->assertExactJson(['groups' => []]);
    }

    public function test_top_bar_lists_only_the_users_pages_and_notices(): void
    {
        $this->seed(MenuSeeder::class);
        $teacher = User::factory()->create(['role' => Role::Teacher]);
        Notice::create(['title' => 'Staff meeting', 'body' => 'Friday', 'audience' => 'teachers', 'published_at' => now()->subHour(), 'created_by' => $teacher->id]);
        Notice::create(['title' => 'Parents evening', 'body' => 'Monday', 'audience' => 'parents', 'published_at' => now()->subHour(), 'created_by' => $teacher->id]);

        $this->actingAs($teacher)->get('/teacher/dashboard')->assertOk()
            ->assertSee('Staff meeting')->assertDontSee('Parents evening')
            // the search box's page list (encoded by @js) has the teacher's pages only
            ->assertSee('\\u0022title\\u0022:\\u0022Student List\\u0022', false)
            ->assertDontSee('\\u0022title\\u0022:\\u0022List Users\\u0022', false);
    }
}
