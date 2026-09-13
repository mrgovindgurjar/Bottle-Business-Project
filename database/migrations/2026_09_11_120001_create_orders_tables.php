<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number', 50)->unique();
                $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
                $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
                $table->unsignedBigInteger('design_id')->nullable();
                $table->date('order_date');
                $table->date('required_date')->nullable();
                $table->string('status', 30)->default('draft')->index();
                $table->decimal('subtotal', 14, 2)->default(0);
                $table->decimal('discount_amount', 14, 2)->default(0);
                $table->decimal('tax_amount', 14, 2)->default(0);
                $table->decimal('shipping_amount', 14, 2)->default(0);
                $table->decimal('other_amount', 14, 2)->default(0);
                $table->decimal('grand_total', 14, 2)->default(0);
                $table->text('delivery_address')->nullable();
                $table->string('delivery_city', 100)->nullable();
                $table->string('delivery_state', 100)->nullable();
                $table->string('delivery_pincode', 10)->nullable();
                $table->text('notes')->nullable();
                $table->text('internal_notes')->nullable();
                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['customer_id', 'status']);
                $table->index(['order_date', 'status']);
            });
        } else {
            if (!Schema::hasColumn('orders', 'quotation_id') && Schema::hasTable('quotations')) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->foreignId('quotation_id')->nullable()->after('id')->constrained('quotations')->nullOnDelete();
                });
            }
        }

        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->unsignedBigInteger('design_id')->nullable();
                $table->text('description');
                $table->decimal('quantity', 14, 3);
                $table->string('unit', 30)->default('bottle');
                $table->decimal('unit_price', 14, 2)->default(0);
                $table->decimal('discount_amount', 14, 2)->default(0);
                $table->decimal('tax_rate', 6, 2)->default(0);
                $table->decimal('tax_amount', 14, 2)->default(0);
                $table->decimal('line_total', 14, 2)->default(0);
                $table->unsignedInteger('sort_order')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
