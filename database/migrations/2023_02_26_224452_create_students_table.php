<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // optional login account
            $table->string('admission_id', 50)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender', 10);      // App\Enums\Gender
            $table->date('date_of_birth');
            $table->string('blood_group', 5);  // App\Enums\BloodGroup
            $table->string('religion', 20);    // App\Enums\Religion
            $table->string('email')->unique();
            $table->string('phone_number', 20);
            $table->foreignId('school_class_id')->constrained()->restrictOnDelete();
            $table->foreignId('section_id')->constrained()->restrictOnDelete();
            $table->string('roll', 20);
            $table->string('photo')->nullable(); // path under public/
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
