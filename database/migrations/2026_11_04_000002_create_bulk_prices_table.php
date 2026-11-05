<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_prices', function (Blueprint $table) {
            $table->id();
            // Indexed through the composite index below (product_id is its leading column).
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
            $table->unsignedInteger('min_quantity');
            // NULL = open-ended top tier ("min_quantity and above").
            $table->unsignedInteger('max_quantity')->nullable();
            $table->decimal('price_per_unit', 12, 2);
            $table->timestamps();

            $table->index(['product_id', 'min_quantity', 'max_quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_prices');
    }
};
