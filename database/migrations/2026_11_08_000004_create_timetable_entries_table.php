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
        // one period of a section's weekly timetable
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // App\Enums\Weekday, Monday = 1
            $table->unsignedTinyInteger('period');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('room', 50)->nullable();
            $table->timestamps();

            $table->unique(['section_id', 'day_of_week', 'period']);
            $table->index(['teacher_id', 'day_of_week', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
    }
};
