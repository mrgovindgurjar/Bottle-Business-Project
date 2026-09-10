<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {

            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->string('type', 30)
                ->default('delivery');

            $table->string('label', 100)
                ->nullable();

            $table->string('contact_name', 150)
                ->nullable();

            $table->string('contact_mobile', 20)
                ->nullable();

            $table->string('address_line1', 300);

            $table->string('address_line2', 300)
                ->nullable();

            $table->string('landmark', 200)
                ->nullable();

            $table->string('city', 100);

            $table->string('state', 100);

            $table->string('pincode', 10);

            $table->string('country', 100)
                ->default('India');

            $table->boolean('is_default')
                ->default(false);

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->index([
                'customer_id',
                'type'
            ]);

            $table->index([
                'city',
                'state'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};