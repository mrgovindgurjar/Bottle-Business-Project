<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('document_sequences')) {
            Schema::create('document_sequences', function (Blueprint $t) {
                $t->id();
                $t->string('document_type', 40);
                $t->unsignedSmallInteger('year');
                $t->unsignedInteger('next_number')->default(1);
                $t->timestamps();
                $t->unique(['document_type', 'year']);
            });
        }

        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $t) {
                $t->id();
                $t->string('supplier_code', 50)->unique();
                $t->string('business_name', 200);
                $t->string('contact_name', 150)->nullable();
                $t->string('mobile', 30)->nullable()->index();
                $t->string('email', 190)->nullable();
                $t->string('gstin', 30)->nullable()->index();
                $t->string('category', 80)->nullable();
                $t->unsignedSmallInteger('payment_terms_days')->default(0);
                $t->string('address')->nullable();
                $t->string('city')->nullable();
                $t->string('state')->nullable();
                $t->string('pincode', 10)->nullable();
                $t->string('status', 20)->default('active')->index();
                $t->text('notes')->nullable();
                $t->timestamps();
                $t->softDeletes();
                $t->index(['business_name', 'status']);
            });
        }

        if (!Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $t) {
                $t->id();
                $t->string('purchase_number', 50)->unique();
                $t->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
                $t->date('purchase_date')->index();
                $t->date('expected_date')->nullable();
                $t->timestamp('received_at')->nullable();
                $t->string('status', 30)->default('draft')->index();
                $t->decimal('subtotal', 14, 2)->default(0);
                $t->string('discount_type', 20)->nullable();
                $t->decimal('discount_value', 14, 2)->default(0);
                $t->decimal('discount_amount', 14, 2)->default(0);
                $t->decimal('tax_rate', 8, 3)->default(0);
                $t->decimal('tax_amount', 14, 2)->default(0);
                $t->decimal('shipping_amount', 14, 2)->default(0);
                $t->decimal('other_amount', 14, 2)->default(0);
                $t->decimal('grand_total', 14, 2)->default(0);
                $t->decimal('paid_amount', 14, 2)->default(0);
                $t->decimal('balance_amount', 14, 2)->default(0);
                $t->string('payment_status', 20)->default('unpaid')->index();
                $t->text('notes')->nullable();
                $t->text('terms_conditions')->nullable();
                $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->softDeletes();
                $t->index(['supplier_id', 'purchase_date']);
            });
        }

        if (!Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $t) {
                $t->id();
                $t->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
                $t->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $t->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
                $t->string('description', 300);
                $t->decimal('quantity', 14, 3);
                $t->decimal('received_quantity', 14, 3)->default(0);
                $t->string('unit', 30)->default('unit');
                $t->decimal('unit_price', 14, 2)->default(0);
                $t->string('discount_type', 20)->nullable();
                $t->decimal('discount_value', 14, 2)->default(0);
                $t->decimal('discount_amount', 14, 2)->default(0);
                $t->decimal('tax_rate', 8, 3)->default(0);
                $t->decimal('tax_amount', 14, 2)->default(0);
                $t->decimal('line_total', 14, 2)->default(0);
                $t->unsignedInteger('sort_order')->default(0);
                $t->json('metadata')->nullable();
                $t->timestamps();
                $t->index(['purchase_id', 'sort_order']);
            });
        }

        if (!Schema::hasTable('supplier_payments')) {
            Schema::create('supplier_payments', function (Blueprint $t) {
                $t->id();
                $t->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
                $t->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
                $t->date('payment_date')->index();
                $t->decimal('amount', 14, 2);
                $t->string('method', 40)->default('cash');
                $t->string('reference', 150)->nullable();
                $t->text('notes')->nullable();
                $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['supplier_id', 'payment_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('document_sequences');
    }
};
