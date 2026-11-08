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
        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('hostel');              // building, e.g. "Boys Hostel"
            $table->string('room_number', 20);
            $table->unsignedTinyInteger('capacity');
            $table->decimal('fee', 10, 2)->default(0); // per term
            $table->timestamps();

            $table->unique(['hostel', 'room_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_rooms');
    }
};
