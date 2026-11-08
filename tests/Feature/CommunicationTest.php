<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Event;
use App\Models\Exam;
use App\Models\Notice;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunicationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::SuperAdmin]);
    }

    public function test_notices_reach_only_their_audience(): void
    {
        foreach (['everyone' => 'School closed Friday', 'teachers' => 'Staff meeting', 'parents' => 'Parent evening'] as $audience => $title) {
            $this->actingAs($this->admin)->post('/notices', ['title' => $title, 'body' => 'Details', 'audience' => $audience])->assertSessionHasNoErrors();
        }
        Notice::create(['title' => 'Old news', 'body' => 'x', 'audience' => 'everyone', 'published_at' => now()->subMonth(), 'expires_on' => today()->subDay()]);
        Notice::create(['title' => 'Coming soon', 'body' => 'x', 'audience' => 'everyone', 'published_at' => now()->addDay()]);

        $parent = User::factory()->create(['role' => Role::Parent]);
        $this->actingAs($parent)->get('/notices')->assertOk()
            ->assertSee('School closed Friday')->assertSee('Parent evening')
            ->assertDontSee('Staff meeting')->assertDontSee('Old news')->assertDontSee('Coming soon');

        $teacher = User::factory()->create(['role' => Role::Teacher]);
        $this->actingAs($teacher)->get('/notices')->assertSee('Staff meeting')->assertDontSee('Parent evening');
        $this->actingAs($teacher)->get('/notices/create')->assertForbidden();
    }

    public function test_events_show_on_the_calendar_and_dashboard(): void
    {
        $this->actingAs($this->admin)->post('/events', [
            'title' => 'Sports Day', 'starts_on' => today()->addDays(3)->toDateString(), 'ends_on' => today()->addDays(4)->toDateString(), 'audience' => 'everyone',
        ])->assertSessionHasNoErrors();
        Event::create(['title' => 'Teacher training', 'starts_on' => today()->addDay(), 'audience' => 'teachers']);

        $student = User::factory()->create(['role' => Role::Student]);
        $this->actingAs($student)->get('/events')->assertOk()->assertSee('Sports Day')->assertDontSee('Teacher training');
        $this->actingAs($student)->get('/student/dashboard')->assertSee('Sports Day');
        $this->actingAs($this->admin)->post('/events', ['title' => 'Bad', 'starts_on' => '2026-05-10', 'ends_on' => '2026-05-01', 'audience' => 'everyone'])
            ->assertSessionHasErrors('ends_on');
    }

    public function test_parent_is_linked_to_a_child_and_sees_only_that_child(): void
    {
        $parent = User::factory()->create(['role' => Role::Parent, 'name' => 'Mrs Sok']);
        $child = Student::factory()->create(['first_name' => 'Rithy', 'last_name' => 'Sok']);
        $stranger = Student::factory()->create();
        $exam = Exam::create(['school_class_id' => $stranger->school_class_id, 'name' => 'Midterm', 'start_date' => '2026-03-01']);

        $this->actingAs($this->admin)->post("/students/{$child->id}/guardians", ['user_id' => $parent->id, 'relationship' => 'mother'])->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post("/students/{$child->id}/guardians", ['user_id' => $parent->id, 'relationship' => 'mother'])->assertSessionHasErrors('user_id');
        $this->actingAs($this->admin)->post("/students/{$child->id}/guardians", ['user_id' => $this->admin->id, 'relationship' => 'father'])->assertSessionHasErrors('user_id');
        $this->actingAs($this->admin)->get('/student/profile/'.$child->id)->assertSee('Mrs Sok')->assertSee('Mother');

        $this->actingAs($parent)->get('/home')->assertRedirect('/parent/dashboard');
        $this->actingAs($parent)->get('/parent/dashboard')->assertOk()->assertSee('Rithy Sok')->assertDontSee($stranger->full_name);
        $this->actingAs($parent)->get("/exams/{$exam->id}/report-card/{$stranger->id}")->assertForbidden();
        $this->assertTrue($parent->canViewStudent($child));

        $this->actingAs($this->admin)->delete("/students/{$child->id}/guardians/{$parent->id}");
        $this->assertFalse($parent->fresh()->canViewStudent($child));
    }

    public function test_parent_dashboard_is_only_for_parents(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::Teacher]))->get('/parent/dashboard')->assertForbidden();
    }
}
