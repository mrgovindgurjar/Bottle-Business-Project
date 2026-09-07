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
       Schema::create('leads', function (Blueprint $table) {
    $table->id();

    $table->string('lead_code', 50)->unique();

    $table->string('business_name', 300);
    $table->string('contact_name', 150)->nullable();

    $table->string('mobile', 20)->nullable();
    $table->string('email', 150)->nullable();

    $table->string('business_type', 100)->nullable();

    $table->string('source', 100)->nullable();

    $table->text('requirement')->nullable();

    $table->unsignedInteger('estimated_quantity')->nullable();

    $table->decimal('estimated_value', 12, 2)->nullable();

    $table->foreignId('assigned_to')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->string('status', 50)->default('new');

    $table->dateTime('next_followup_at')->nullable();

    $table->text('notes')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
