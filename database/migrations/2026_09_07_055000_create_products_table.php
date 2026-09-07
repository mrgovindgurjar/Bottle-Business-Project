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
      Schema::create('products', function (Blueprint $table) {
    $table->id();

    $table->string('name', 200);

    $table->string('sku', 100)->unique();

    $table->unsignedInteger('bottle_size_ml');

    $table->string('bottle_type', 50)->nullable();

    $table->string('material', 50)->nullable();

    $table->unsignedInteger('units_per_box')->default(1);

    $table->string('unit', 30)->default('bottle');

    $table->text('description')->nullable();

    $table->string('status', 30)->default('active');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
