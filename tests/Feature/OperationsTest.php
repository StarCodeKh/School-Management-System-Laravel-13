<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\HostelAllocation;
use App\Models\HostelRoom;
use App\Models\Setting;
use App\Models\Student;
use App\Models\TransportAssignment;
use App\Models\TransportRoute;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::SuperAdmin]);
    }

    public function test_books_are_issued_within_available_copies_and_fined_when_late(): void
    {
        Setting::store(['library_fine_per_day' => '0.50']);
        $this->actingAs($this->admin)->post('/books', ['title' => 'Physics Basics', 'author' => 'Chan', 'quantity' => 1])->assertSessionHasNoErrors();
        $book = Book::firstOrFail();
        [$first, $second] = [Student::factory()->create(), Student::factory()->create()];

        $this->actingAs($this->admin)->post('/library/loans', [
            'book_id' => $book->id, 'student_id' => $first->id, 'issued_on' => today()->subDays(20)->toDateString(), 'due_on' => today()->subDays(6)->toDateString(),
        ])->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post('/library/loans', [
            'book_id' => $book->id, 'student_id' => $second->id, 'issued_on' => today()->toDateString(), 'due_on' => today()->addWeek()->toDateString(),
        ])->assertSessionHasErrors('book_id'); // only one copy

        $loan = BookLoan::firstOrFail();
        $this->assertTrue($loan->isOverdue());
        $this->actingAs($this->admin)->get('/library/loans?tab=overdue')->assertOk()->assertSee('Physics Basics')->assertSee('6 days late');

        $this->actingAs($this->admin)->post("/library/loans/{$loan->id}/return")->assertSessionHas('success');
        $this->assertSame('3.00', $loan->fresh()->fine);
        $this->assertSame(1, $book->fresh()->available());

        // quantity cannot drop below copies on loan, and loaned books are not deleted
        $this->actingAs($this->admin)->delete("/books/{$book->id}")->assertSessionHas('error');
        $this->assertModelExists($book);
    }

    public function test_transport_respects_vehicle_capacity(): void
    {
        $this->actingAs($this->admin)->post('/transport/vehicles', ['registration_number' => 'PP-1234', 'capacity' => 1])->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post('/transport/routes', ['name' => 'Route 1', 'vehicle_id' => Vehicle::first()->id, 'stops' => "Market\nRiverside", 'fare' => ''])->assertSessionHasNoErrors();
        $route = TransportRoute::firstOrFail();
        $this->assertSame(['Market', 'Riverside'], $route->stopList());
        $this->assertSame('0.00', $route->fare);

        [$a, $b] = [Student::factory()->create(), Student::factory()->create()];
        $this->actingAs($this->admin)->post("/transport/routes/{$route->id}/students", ['student_id' => $a->id, 'pickup_stop' => 'Market'])->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post("/transport/routes/{$route->id}/students", ['student_id' => $b->id])->assertSessionHasErrors('student_id');
        $this->actingAs($this->admin)->post("/transport/routes/{$route->id}/students", ['student_id' => $a->id])->assertSessionHasErrors('student_id');

        $this->assertSame(1, TransportAssignment::count());
        $this->actingAs($this->admin)->get("/transport/routes/{$route->id}")->assertOk()->assertSee($a->full_name);
        $this->actingAs($this->admin)->get('/student/profile/'.$a->id)->assertSee('Route 1')->assertSee('Market');
    }

    public function test_hostel_rooms_do_not_overfill(): void
    {
        $this->actingAs($this->admin)->post('/hostel/rooms', ['hostel' => 'Girls Hostel', 'room_number' => '101', 'capacity' => 1])->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post('/hostel/rooms', ['hostel' => 'Girls Hostel', 'room_number' => '101', 'capacity' => 2])->assertSessionHasErrors('room_number');
        $room = HostelRoom::firstOrFail();
        [$a, $b] = [Student::factory()->create(), Student::factory()->create()];

        $this->actingAs($this->admin)->post('/hostel/allocations', ['student_id' => $a->id, 'hostel_room_id' => $room->id])->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post('/hostel/allocations', ['student_id' => $b->id, 'hostel_room_id' => $room->id])->assertSessionHasErrors('hostel_room_id');
        $this->actingAs($this->admin)->delete("/hostel/rooms/{$room->id}")->assertSessionHas('error');

        $this->actingAs($this->admin)->get('/hostel')->assertOk()->assertSee($a->full_name);
        $this->actingAs($this->admin)->delete('/hostel/allocations/'.HostelAllocation::first()->id);
        $this->assertSame(0, HostelAllocation::count());
    }

    public function test_operations_are_admin_only(): void
    {
        $teacher = User::factory()->create(['role' => Role::Teacher]);

        foreach (['/books', '/library/loans', '/transport', '/hostel', '/setting/library'] as $uri) {
            $this->actingAs($teacher)->get($uri)->assertForbidden();
            $this->actingAs($this->admin)->get($uri)->assertOk();
        }
    }
}
