<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Initial administrator
    |--------------------------------------------------------------------------
    |
    | The Super Admin account created by `php artisan db:seed`. Set these in
    | .env before seeding, and change the password after the first login.
    |
    */

    'admin' => [
        'name' => env('ADMIN_NAME', 'Super Admin'),
        'email' => env('ADMIN_EMAIL', 'admin@system.com'),
        'password' => env('ADMIN_PASSWORD', 'Password@123'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo data
    |--------------------------------------------------------------------------
    |
    | Sizes for `php artisan school:demo`, which fills every module with
    | realistic sample data (each value can also be passed as an option).
    | The same seed always produces the same school. Never run it on a
    | production database.
    |
    */

    'demo' => [
        'classes' => ['7', '8', '9', '10', '11', '12'],
        'sections' => ['A', 'B'],
        'students_per_section' => (int) env('DEMO_STUDENTS_PER_SECTION', 18),
        'teachers' => (int) env('DEMO_TEACHERS', 0),      // 0 = enough for the timetable (about 18 periods a week each)
        'months' => (int) env('DEMO_MONTHS', 3),         // months of attendance, fees, payroll and expenses
        'seed' => (int) env('DEMO_SEED', 2026),
        'password' => env('DEMO_PASSWORD', 'password'),  // password of every demo account
    ],

];
