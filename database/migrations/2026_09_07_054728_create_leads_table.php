<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->string('lead_code', 50)->unique();

            $table->string('business_name', 300);

            $table->string('contact_name', 150)->nullable();

            $table->string('mobile', 20)->nullable();

            $table->string('email', 190)->nullable();

            $table->string('business_type', 100)->nullable();

            $table->string('source', 100)->nullable();

            $table->text('requirement')->nullable();

            $table->unsignedInteger('estimated_quantity')->nullable();

            $table->string('quantity_unit', 30)
                ->default('bottle');

            $table->string('order_frequency', 50)->nullable();

            $table->decimal(
                'estimated_value',
                15,
                2
            )->nullable();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('status', 50)
                ->default('new');

            $table->dateTime('next_followup_at')
                ->nullable();

            $table->dateTime('last_contacted_at')
                ->nullable();

            $table->string('address', 500)
                ->nullable();

            $table->string('city', 100)
                ->nullable();

            $table->string('state', 100)
                ->nullable();

            $table->string('pincode', 10)
                ->nullable();

            $table->text('notes')->nullable();

            $table->text('lost_reason')->nullable();

            $table->foreignId('converted_customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->timestamps();

            $table->softDeletes();

            $table->index('status');
            $table->index('source');
            $table->index('business_type');
            $table->index('assigned_to');
            $table->index('next_followup_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};