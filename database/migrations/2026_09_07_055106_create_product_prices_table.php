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
      Schema::create('product_prices', function (Blueprint $table) {
    $table->id();

    $table->foreignId('product_id')
        ->constrained('products')
        ->cascadeOnDelete();

    $table->foreignId('customer_id')
        ->nullable()
        ->constrained('customers')
        ->nullOnDelete();

    $table->string('pricing_type', 50)->default('standard');

    $table->unsignedInteger('min_quantity')->default(1);

    $table->unsignedInteger('max_quantity')->nullable();

    $table->decimal('unit_price', 10, 2);

    $table->date('effective_from')->nullable();

    $table->date('effective_to')->nullable();

    $table->string('status', 30)->default('active');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
