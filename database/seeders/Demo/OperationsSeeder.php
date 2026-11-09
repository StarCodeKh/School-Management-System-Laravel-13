<?php

namespace Database\Seeders\Demo;

use App\Enums\Gender;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\HostelAllocation;
use App\Models\HostelRoom;
use App\Models\Student;
use App\Models\TransportAssignment;
use App\Models\TransportRoute;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

/**
 * Library books with active, overdue and returned loans; buses and routes around Phnom Penh
 * with riders; boys' and girls' hostels with residents.
 */
class OperationsSeeder extends Seeder
{
    /** title => [author, category] */
    private const BOOKS = [
        'Tum Teav' => ['Santhor Mok', 'Khmer Literature'],
        'Sophat' => ['Rim Kin', 'Khmer Literature'],
        'Kolab Pailin' => ['Nhok Thaem', 'Khmer Literature'],
        'The Little Prince' => ['Antoine de Saint-Exupéry', 'Fiction'],
        'Charlotte\'s Web' => ['E. B. White', 'Fiction'],
        'Harry Potter and the Philosopher\'s Stone' => ['J. K. Rowling', 'Fiction'],
        'Wonder' => ['R. J. Palacio', 'Fiction'],
        'Animal Farm' => ['George Orwell', 'Fiction'],
        'A Brief History of Time' => ['Stephen Hawking', 'Science'],
        'The Diary of a Young Girl' => ['Anne Frank', 'History'],
        'Cambodia: A History' => ['David Chandler', 'History'],
        'First They Killed My Father' => ['Loung Ung', 'History'],
        'Oxford English Dictionary for Schools' => ['Oxford University Press', 'Reference'],
        'Khmer-English Dictionary' => ['Robert K. Headley', 'Reference'],
        'Algebra Made Simple' => ['Ministry of Education', 'Mathematics'],
        'Geometry Workbook, Grade 9' => ['Ministry of Education', 'Mathematics'],
        'Physics for Secondary Schools' => ['Ministry of Education', 'Science'],
        'Chemistry Lab Handbook' => ['Ministry of Education', 'Science'],
        'Introduction to Programming with Python' => ['Eric Matthes', 'Computers'],
        'Atlas of the World' => ['National Geographic', 'Reference'],
    ];

    private const ROUTES = [
        'Route 1 - Toul Kork' => ['Toul Kork Market', 'Boeung Kak', 'Russian Embassy', 'Wat Phnom'],
        'Route 2 - Sen Sok' => ['Aeon Mall Sen Sok', 'Phnom Penh Thmey', 'Kilometer 6', 'Olympic Stadium'],
        'Route 3 - Chamkar Mon' => ['Boeung Keng Kang', 'Tuol Tom Poung', 'Chbar Ampov Bridge', 'Independence Monument'],
    ];

    public function run(): void
    {
        $this->library();
        $this->transport();
        $this->hostel();
    }

    private function library(): void
    {
        $faker = DemoData::faker();
        foreach (self::BOOKS as $title => [$author, $category]) {
            Book::create([
                'title' => $title,
                'author' => $author,
                'category' => $category,
                'isbn' => '978'.$faker->numerify('##########'),
                'publisher' => $faker->randomElement(['Ponleu Khmer', 'Penguin', 'Oxford University Press', 'Ministry of Education']),
                'shelf' => $faker->randomLetter().'-'.$faker->numberBetween(1, 9),
                'quantity' => $faker->numberBetween(2, 8),
            ]);
        }

        $books = Book::all();
        $students = DemoData::shuffle(Student::orderBy('id')->pluck('id'))->take(40);
        $librarian = User::where('position', 'Librarian')->value('id');
        $loanDays = (int) setting('library_loan_days', 14);
        $finePerDay = (float) setting('library_fine_per_day', 0.25);
        $out = []; // book id => copies on loan

        foreach ($students as $i => $studentId) {
            $available = fn (Book $book) => ($out[$book->id] ?? 0) < $book->quantity;
            $book = $books->first(fn (Book $book) => $available($book) && $faker->boolean(60)) ?? $books->first($available);
            if (! $book) {
                break; // every copy is out
            }
            // a third returned (some late, with a fine), most still out, a few overdue
            $kind = DemoData::weighted(['returned' => 35, 'active' => 50, 'overdue' => 15]);
            $issued = match ($kind) {
                'returned' => today()->subDays($faker->numberBetween(20, 80)),
                'active' => today()->subDays($faker->numberBetween(0, $loanDays - 1)),
                'overdue' => today()->subDays($loanDays + $faker->numberBetween(2, 20)),
            };
            $due = $issued->copy()->addDays($loanDays);
            $returned = $kind === 'returned' ? $issued->copy()->addDays($faker->numberBetween(3, $loanDays + 6)) : null;
            $lateDays = $returned && $returned->gt($due) ? $due->diffInDays($returned) : 0;
            if ($kind !== 'returned') {
                $out[$book->id] = ($out[$book->id] ?? 0) + 1;
            }

            BookLoan::create([
                'book_id' => $book->id,
                'student_id' => $studentId,
                'issued_on' => $issued,
                'due_on' => $due,
                'returned_on' => $returned,
                'fine' => round($lateDays * $finePerDay, 2),
                'issued_by' => $librarian,
            ]);
        }
    }

    private function transport(): void
    {
        $faker = DemoData::faker();
        $vehicles = collect(['2A-1024', '2B-3311', '2C-7745', '2A-8820'])->map(function (string $plate) use ($faker) {
            [$given, $family] = DemoData::name(Gender::Male);

            return Vehicle::create([
                'registration_number' => 'PP '.$plate,
                'model' => $faker->randomElement(['Toyota Coaster', 'Hyundai County', 'Ford Transit']),
                'capacity' => $faker->randomElement([16, 24, 29]),
                'driver_name' => "{$given} {$family}",
                'driver_phone' => DemoData::phone(),
            ]);
        });

        $routes = collect(self::ROUTES)->keys()->map(fn (string $name, int $i) => TransportRoute::create([
            'name' => $name,
            'vehicle_id' => $vehicles[$i]->id,
            'fare' => $faker->randomElement([80, 90, 100]),
            'stops' => implode("\n", self::ROUTES[$name]),
        ]));

        // about one student in five rides a bus, within each bus's capacity
        foreach ($routes as $route) {
            $riders = DemoData::shuffle(Student::doesntHave('transportAssignment')->orderBy('id')->pluck('id'))
                ->take(min($route->vehicle->capacity, (int) ceil(Student::count() / 5 / $routes->count())));
            foreach ($riders as $studentId) {
                TransportAssignment::create([
                    'student_id' => $studentId,
                    'transport_route_id' => $route->id,
                    'pickup_stop' => $faker->randomElement($route->stopList()),
                ]);
            }
        }
    }

    private function hostel(): void
    {
        $faker = DemoData::faker();
        foreach (['Boys Hostel' => Gender::Male, 'Girls Hostel' => Gender::Female] as $hostel => $gender) {
            $rooms = collect(range(1, 6))->map(fn (int $n) => HostelRoom::create([
                'hostel' => $hostel,
                'room_number' => (string) (100 + $n),
                'capacity' => $n <= 4 ? 4 : 2,
                'fee' => $n <= 4 ? 150 : 220,
            ]));

            // fill most beds, leaving some rooms with space
            $residents = DemoData::shuffle(Student::where('gender', $gender)->doesntHave('hostelAllocation')->orderBy('id')->pluck('id'))
                ->take((int) floor($rooms->sum('capacity') * 0.75));
            foreach ($residents as $studentId) {
                $room = $rooms->first(fn (HostelRoom $room) => $room->allocations()->count() < $room->capacity);
                if (! $room) {
                    break;
                }
                HostelAllocation::create([
                    'student_id' => $studentId,
                    'hostel_room_id' => $room->id,
                    'allocated_on' => today()->subDays($faker->numberBetween(10, 120)),
                ]);
            }
        }
    }
}
