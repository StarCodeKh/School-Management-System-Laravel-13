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
        // an exam sitting for one class, e.g. "Midterm 2026" for class 10
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('term', 50)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->unsignedSmallInteger('max_marks')->default(100); // per subject
            $table->timestamps();

            $table->unique(['school_class_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
