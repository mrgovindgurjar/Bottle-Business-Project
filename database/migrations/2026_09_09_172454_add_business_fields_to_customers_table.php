<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {

            $table->string('business_type', 100)
                ->nullable()
                ->after('business_name');

            $table->string('gstin', 15)
                ->nullable()
                ->unique()
                ->after('business_type');

            $table->string('pan_number', 10)
                ->nullable()
                ->after('gstin');

            $table->unsignedSmallInteger('payment_terms_days')
                ->default(0)
                ->after('pan_number');

            $table->decimal('credit_limit', 12, 2)
                ->default(0)
                ->after('payment_terms_days');

            $table->string('currency', 3)
                ->default('INR')
                ->after('credit_limit');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {

            $table->dropUnique([
                'customers_gstin_unique'
            ]);

            $table->dropColumn([
                'business_type',
                'gstin',
                'pan_number',
                'payment_terms_days',
                'credit_limit',
                'currency',
            ]);
        });
    }
};