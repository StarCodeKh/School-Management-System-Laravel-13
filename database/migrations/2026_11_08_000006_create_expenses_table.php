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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category', 30)->index();      // App\Enums\ExpenseCategory
            $table->decimal('amount', 12, 2);
            $table->date('expense_date')->index();
            $table->string('paid_to')->nullable();
            $table->string('payment_method', 30);         // App\Enums\PaymentMethod
            $table->string('reference', 100)->nullable(); // receipt or transfer number
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
