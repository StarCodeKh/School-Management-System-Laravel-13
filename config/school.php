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

];
