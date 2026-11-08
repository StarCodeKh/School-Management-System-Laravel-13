<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(MenuSeeder::class);

        // Local development admin. Change the password after the first login.
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'role' => Role::SuperAdmin,
            'password' => 'Password@123',
        ]);

        // Starter fee types used by the fee collection form
        foreach (['Tuition Fees', 'Exam Fees', 'Class Test', 'Hostel Fees', 'Transport Fees', 'Mess Fees'] as $name) {
            FeeType::create(['name' => $name]);
        }

        // Starter classes 10-12 with sections A-C; manage them later on the Classes page.
        foreach (['10', '11', '12'] as $order => $name) {
            SchoolClass::create(['name' => $name, 'sort_order' => $order + 1])
                ->sections()->createMany([['name' => 'A'], ['name' => 'B'], ['name' => 'C']]);
        }
    }
}
