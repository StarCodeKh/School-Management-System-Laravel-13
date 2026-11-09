<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\Attendance;
use App\Models\Book;
use App\Models\ExamResult;
use App\Models\FeeCollection;
use App\Models\Invoice;
use App\Models\Payroll;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\Demo\PeopleSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;

class SeedDemoData extends Command
{
    protected $signature = 'school:demo
        {--fresh : Drop every table and migrate again before loading the demo}
        {--students= : Students per section (default from config/school.php)}
        {--teachers= : Number of teachers (default: enough for the timetable)}
        {--months= : Months of attendance, fees, payroll and expenses history}
        {--seed= : Random seed; the same seed always builds the same school}
        {--force : Allow running in production}';

    protected $description = 'Fill every module with a realistic sample school (teachers, students, parents, marks, fees, ...)';

    public function handle(): int
    {
        if ($this->laravel->isProduction() && ! $this->option('force')) {
            $this->components->error('This is a production environment. Demo data would mix with real records; pass --force if you are sure.');

            return self::FAILURE;
        }

        foreach (['students' => 'students_per_section', 'teachers' => 'teachers', 'months' => 'months', 'seed' => 'seed'] as $option => $key) {
            if ($this->option($option) !== null) {
                if (! ctype_digit((string) $this->option($option)) || (int) $this->option($option) < 1) {
                    $this->components->error("--{$option} must be a whole number of at least 1.");

                    return self::FAILURE;
                }
                config(["school.demo.{$key}" => (int) $this->option($option)]);
            }
        }

        if ($this->option('fresh')) {
            if (! $this->option('no-interaction') && ! $this->components->confirm('Delete ALL data in '.config('database.default').' and rebuild it?', true)) {
                return self::FAILURE;
            }
            $this->call('migrate:fresh', ['--force' => true]);
        }

        $started = microtime(true);
        $this->components->info('Building the demo school...');
        try {
            $this->call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);
        } catch (\RuntimeException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->summary(microtime(true) - $started);

        return self::SUCCESS;
    }

    private function summary(float $seconds): void
    {
        $this->components->twoColumnDetail('<fg=green;options=bold>Created</>', sprintf('in %.1fs', $seconds));
        foreach ([
            'Students' => Student::count(),
            'Teachers' => User::where('role', Role::Teacher)->count(),
            'Parents' => User::where('role', Role::Parent)->count(),
            'Attendance records' => Attendance::count(),
            'Exam marks' => ExamResult::count(),
            'Fee payments' => FeeCollection::count(),
            'Invoices' => Invoice::count(),
            'Salary records' => Payroll::count(),
            'Library books' => Book::count(),
        ] as $label => $count) {
            $this->components->twoColumnDetail($label, number_format($count));
        }

        $this->newLine();
        $this->components->info('Log in with any of these (password: '.config('school.demo.password').')');
        $this->table(['Role', 'Email'], collect([config('school.admin.email') => Role::SuperAdmin] + PeopleSeeder::LOGINS)
            ->map(fn (Role $role, string $email) => [$role->label(), $email])->values()->all());
        $this->line('  <fg=gray>The Super Admin uses the password from ADMIN_PASSWORD in .env.</>');
    }
}
