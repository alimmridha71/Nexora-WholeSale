<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('base_price', 12, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->string('sku', 64)->unique();
            $table->timestamps();

            $table->index(['name', 'base_price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
