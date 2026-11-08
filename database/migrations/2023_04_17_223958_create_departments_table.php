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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->nullable()->unique(); // display code, e.g. DEP-00001
            $table->string('department_name')->unique();
            // the head's user account (must be a teacher); pointing at users, not teachers, avoids a
            // departments <-> teachers foreign key cycle now that teachers.department_id exists
            $table->foreignId('head_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('department_start_date');
            $table->unsignedInteger('no_of_students')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
