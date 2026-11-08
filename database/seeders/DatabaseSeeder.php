<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. Safe to run more than once:
     * existing rows are kept, missing ones are added.
     */
    public function run(): void
    {
        $this->call(MenuSeeder::class);

        // Initial Super Admin from config/school.php (ADMIN_* in .env).
        // An existing account with this email is left unchanged.
        $admin = config('school.admin');
        User::firstOrCreate(['email' => $admin['email']], [
            'name' => $admin['name'],
            'role' => Role::SuperAdmin,
            'status' => UserStatus::Active,
            'join_date' => now(),
            'email_verified_at' => now(),
            'password' => $admin['password'],
        ]);

        // Starter fee types used by the fee collection form
        foreach (['Tuition Fees', 'Exam Fees', 'Class Test', 'Hostel Fees', 'Transport Fees', 'Mess Fees'] as $name) {
            FeeType::firstOrCreate(['name' => $name]);
        }

        // Starter classes 10-12 with sections A-C; manage them later on the Classes page.
        foreach (['10', '11', '12'] as $order => $name) {
            $class = SchoolClass::firstOrCreate(['name' => $name], ['sort_order' => $order + 1]);
            foreach (['A', 'B', 'C'] as $section) {
                $class->sections()->firstOrCreate(['name' => $section]);
            }
        }
    }
}
