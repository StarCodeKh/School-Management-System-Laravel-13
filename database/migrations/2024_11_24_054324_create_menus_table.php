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
        // Sidebar menu tree; rows are created by Database\Seeders\MenuSeeder
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('menus')->cascadeOnDelete();
            $table->string('title');
            $table->string('icon', 50)->nullable();       // name for <x-icon>, e.g. "home"
            $table->string('route')->nullable();          // named route the item links to
            $table->json('active_routes')->nullable();    // route names that highlight the item
            $table->string('pattern')->nullable();        // URL patterns that highlight it, "a/*|b/*"
            $table->string('ability', 50)->nullable();    // Gate ability required to see it
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
