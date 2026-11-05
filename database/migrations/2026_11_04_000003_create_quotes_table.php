<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            // Random, non-sequential public reference (e.g. NXQ-7KD9QX2M).
            $table->string('reference', 20)->unique();
            // Restrict: a user with quote history cannot be hard-deleted.
            // Indexed through the composite index below (user_id is its leading column).
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();
            // [{"product_id": 1, "quantity": 5000, "notes": "..."}]
            $table->json('product_details_json');
            $table->decimal('target_price', 12, 2)->nullable();
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected', 'expired'])
                ->default('pending');
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
