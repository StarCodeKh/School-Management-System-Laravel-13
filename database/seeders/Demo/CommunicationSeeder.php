<?php

namespace Database\Seeders\Demo;

use App\Enums\Audience;
use App\Enums\Role;
use App\Models\Event;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Notice board posts for every audience (one pinned, one expired, one scheduled) and a calendar
 * of school events and Cambodian public holidays around today.
 */
class CommunicationSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('role', Role::SuperAdmin)->value('id');
        $this->notices($author);
        $this->events($author);
    }

    private function notices(?int $author): void
    {
        $notices = [
            // title, body, audience, pinned, published (days from today), expires (days from today)
            ['Welcome to the new term', 'Welcome back, everyone! Classes start at 7:00 sharp. Please check your timetable and bring your student ID card every day.', Audience::Everyone, true, -30, null],
            ['Mid-term results are published', 'Mid-term examination results are now available. Parents can see them on the parent dashboard; students on their own dashboard.', Audience::Everyone, false, -10, 20],
            ['Staff meeting on Friday', 'All teaching staff: weekly meeting on Friday at 14:30 in the library. Agenda: final exam timetable and report cards.', Audience::Teachers, false, -3, 4],
            ['Final exam timetable', 'The final examination timetable is on the Exams page. Revision classes run every afternoon in the two weeks before the exams.', Audience::Students, false, -2, 30],
            ['Parent-teacher conference', 'You are invited to meet your child\'s teachers next Saturday from 8:00 to 11:00. No appointment is needed.', Audience::Parents, false, -1, 10],
            ['Fee payment reminder', 'Second-instalment tuition fees are due by the end of this month. Payments can be made at the office or by bank transfer.', Audience::Parents, false, -5, 25],
            ['Payroll processed', 'Salaries for last month have been transferred. Contact the accountant if you have any questions about your payslip.', Audience::Staff, false, -8, 7],
            ['Library closed for stock-taking', 'The library was closed for annual stock-taking. Thank you for returning all borrowed books on time.', Audience::Everyone, false, -40, -20],
            ['Sports day volunteers needed', 'We are looking for parent volunteers to help on sports day. Please sign up at the office.', Audience::Parents, false, 3, 20],
        ];

        foreach ($notices as [$title, $body, $audience, $pinned, $published, $expires]) {
            Notice::create([
                'title' => $title,
                'body' => $body,
                'audience' => $audience,
                'is_pinned' => $pinned,
                'published_at' => now()->addDays($published)->setTime(7, 30),
                'expires_on' => $expires === null ? null : today()->addDays($expires),
                'created_by' => $author,
            ]);
        }
    }

    private function events(?int $author): void
    {
        $year = today()->year;
        $events = [
            // school events, relative to today
            ['Sports Day', 'Track and field competitions between classes. Wear your sports uniform.', 'Sports field', 12, 12, Audience::Everyone],
            ['Science Fair', 'Grade 9-12 students present their science projects.', 'Main hall', 26, 27, Audience::Everyone],
            ['Parent-Teacher Conference', 'Meet the teachers and discuss your child\'s progress.', 'Classrooms', 6, 6, Audience::Parents],
            ['Final Examinations', 'Final examinations for all classes.', 'Examination halls', 25, 31, Audience::Students],
            ['Teacher Training Day', 'Professional development workshop. No classes for students.', 'Library', 18, 18, Audience::Teachers],
            ['School Trip to Angkor Wat', 'Day trip for grade 8 students. Buses leave at 6:00.', 'Siem Reap', -14, -14, Audience::Students],
            ['Book Week', 'Reading challenges, a book swap and author visits.', 'Library', -35, -31, Audience::Everyone],
        ];

        foreach ($events as [$title, $description, $location, $start, $end, $audience]) {
            Event::create([
                'title' => $title,
                'description' => $description,
                'location' => $location,
                'starts_on' => today()->addDays($start),
                'ends_on' => $end === $start ? null : today()->addDays($end),
                'audience' => $audience,
                'created_by' => $author,
            ]);
        }
        foreach ([$year, $year + 1] as $y) {
            foreach (DemoData::HOLIDAYS as [$title, $from, $to]) {
                Event::create([
                    'title' => $title.' (public holiday)',
                    'description' => 'School closed.',
                    'starts_on' => Carbon::parse("{$y}-{$from}"),
                    'ends_on' => $from === $to ? null : Carbon::parse("{$y}-{$to}"),
                    'audience' => Audience::Everyone,
                    'created_by' => $author,
                ]);
            }
        }
    }
}
