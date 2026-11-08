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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); // name/email live on users
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gender', 10); // App\Enums\Gender
            $table->date('date_of_birth');
            $table->string('qualification');
            $table->unsignedTinyInteger('experience')->default(0); // years
            $table->string('phone_number', 20);
            $table->string('address');
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('zip_code', 20);
            $table->string('country', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
