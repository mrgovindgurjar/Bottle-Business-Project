<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'business_type')) {
                $table->string('business_type', 100)->nullable()->after('business_name');
            }
            if (!Schema::hasColumn('customers', 'gstin')) {
                $table->string('gstin', 15)->nullable()->unique()->after('business_type');
            }
            if (!Schema::hasColumn('customers', 'pan_number')) {
                $table->string('pan_number', 10)->nullable()->after('gstin');
            }
            if (!Schema::hasColumn('customers', 'payment_terms_days')) {
                $table->unsignedSmallInteger('payment_terms_days')->default(0)->after('pan_number');
            }
            if (!Schema::hasColumn('customers', 'credit_limit')) {
                $table->decimal('credit_limit', 12, 2)->default(0)->after('payment_terms_days');
            }
            if (!Schema::hasColumn('customers', 'currency')) {
                $table->string('currency', 3)->default('INR')->after('credit_limit');
            }
        });
    }

    public function down(): void
    {
        $columns = array_filter([
            Schema::hasColumn('customers', 'business_type') ? 'business_type' : null,
            Schema::hasColumn('customers', 'gstin') ? 'gstin' : null,
            Schema::hasColumn('customers', 'pan_number') ? 'pan_number' : null,
            Schema::hasColumn('customers', 'payment_terms_days') ? 'payment_terms_days' : null,
            Schema::hasColumn('customers', 'credit_limit') ? 'credit_limit' : null,
            Schema::hasColumn('customers', 'currency') ? 'currency' : null,
        ]);

        if ($columns) {
            Schema::table('customers', function (Blueprint $table) use ($columns) {
                if (in_array('gstin', $columns, true)) {
                    $table->dropUnique(['customers_gstin_unique']);
                }
                $table->dropColumn($columns);
            });
        }
    }
};
