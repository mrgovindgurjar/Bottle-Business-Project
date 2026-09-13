<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('batch_sequences')) {
            Schema::create('batch_sequences', function (Blueprint $table) {
                $table->id(); $table->year('year'); $table->unsignedBigInteger('last_number')->default(0); $table->timestamps(); $table->unique('year');
            });
        }

        if (!Schema::hasTable('batches')) {
            Schema::create('batches', function (Blueprint $table) {
                $table->id();
                $table->string('batch_number', 60)->unique();
                $table->foreignId('production_order_id')->constrained('production_orders')->restrictOnDelete();
                $table->foreignId('production_order_item_id')->nullable()->constrained('production_order_items')->nullOnDelete();
                $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
                $table->unsignedBigInteger('design_id')->nullable();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->date('manufacturing_date');
                $table->date('expiry_date')->nullable();
                $table->decimal('produced_quantity', 14, 3)->default(0);
                $table->decimal('rejected_quantity', 14, 3)->default(0);
                $table->decimal('available_quantity', 14, 3)->default(0);
                $table->string('status', 30)->default('quality_pending')->index();
                $table->string('quality_status', 30)->default('pending')->index();
                $table->text('quality_notes')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('released_at')->nullable();
                $table->timestamp('blocked_at')->nullable();
                $table->text('blocked_reason')->nullable();
                $table->timestamps(); $table->softDeletes();
                $table->index(['product_id', 'status']); $table->index(['customer_id', 'status']); $table->index(['manufacturing_date', 'expiry_date']);
            });
        }

        if (!Schema::hasTable('batch_allocations')) {
            Schema::create('batch_allocations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                // Delivery module is intentionally implemented later; keep this nullable now.
                $table->unsignedBigInteger('delivery_id')->nullable();
                $table->decimal('quantity', 14, 3);
                $table->timestamp('allocated_at');
                $table->foreignId('allocated_by')->constrained('users')->restrictOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['customer_id', 'allocated_at']); $table->index(['order_id', 'batch_id']);
            });
        }

        // Add the FK only when a Delivery table already exists. The upcoming Delivery
        // module can add the constraint later without blocking this migration.
        if (Schema::hasTable('deliveries') && Schema::hasTable('batch_allocations')) {
            Schema::table('batch_allocations', function (Blueprint $table) {
                $table->foreign('delivery_id')->references('id')->on('deliveries')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_allocations'); Schema::dropIfExists('batches'); Schema::dropIfExists('batch_sequences');
    }
};
