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
       Schema::create('customers', function (Blueprint $table) {

    $table->id();

    $table->string('customer_code', 150)->unique();

    $table->foreignId('user_id')
          ->unique()
          ->constrained('users')
          ->cascadeOnDelete();

    $table->string('business_name', 300);

    $table->string('address')->nullable();
    $table->string('city')->nullable();
    $table->string('state')->nullable();
    $table->string('pincode', 10)->nullable();

    $table->string('source')->nullable();

    $table->enum('status', [
        'active',
        'inactive',
        'blocked'
    ])->default('active');

    $table->text('notes')->nullable();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
