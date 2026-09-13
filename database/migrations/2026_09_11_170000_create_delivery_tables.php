<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('delivery_sequences')) {
            Schema::create('delivery_sequences', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('year')->unique();
                $table->unsignedBigInteger('last_number')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('deliveries')) {
            Schema::create('deliveries', function (Blueprint $table) {
                $table->id();
                $table->string('delivery_number', 60)->unique();
                $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
                $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
                $table->date('delivery_date');
                $table->date('scheduled_date')->nullable();
                $table->string('status', 30)->default('draft')->index();
                $table->text('delivery_address')->nullable();
                $table->string('delivery_city', 100)->nullable();
                $table->string('delivery_state', 100)->nullable();
                $table->string('delivery_pincode', 10)->nullable();
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->string('driver_name', 150)->nullable();
                $table->string('driver_mobile', 30)->nullable();
                $table->string('vehicle_number', 50)->nullable();
                $table->text('notes')->nullable();
                $table->text('dispatch_notes')->nullable();
                $table->string('receiver_name', 150)->nullable();
                $table->string('receiver_mobile', 30)->nullable();
                $table->timestamp('dispatched_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->text('failed_reason')->nullable();
                $table->string('proof_path')->nullable();
                $table->string('signature_path')->nullable();
                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['customer_id', 'status']);
                $table->index(['order_id', 'status']);
                $table->index(['delivery_date', 'status']);
            });
        }

        if (!Schema::hasTable('delivery_items')) {
            Schema::create('delivery_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delivery_id')->constrained('deliveries')->cascadeOnDelete();
                $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
                $table->text('description')->nullable();
                $table->decimal('quantity', 14, 3);
                $table->decimal('delivered_quantity', 14, 3)->default(0);
                $table->string('unit', 30)->default('bottle');
                $table->unsignedBigInteger('inventory_movement_id')->nullable()->index();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['delivery_id', 'product_id']);
                $table->index(['batch_id', 'delivery_id']);
            });
        }

        // Batch module deliberately left delivery_id without an FK so Batch can migrate
        // before Delivery. This migration is the point where the relationship is completed.
        if (Schema::hasTable('batch_allocations') && ! $this->foreignExists('batch_allocations', 'batch_allocations_delivery_id_foreign')) {
            Schema::table('batch_allocations', function (Blueprint $table) {
                $table->foreign('delivery_id')->references('id')->on('deliveries')->nullOnDelete();
            });
        }
    }

    private function foreignExists(string $table, string $constraint): bool
    {
        try {
            $database = Schema::getConnection()->getDatabaseName();
            $row = Schema::getConnection()->selectOne(
                'select count(*) as aggregate from information_schema.TABLE_CONSTRAINTS where CONSTRAINT_SCHEMA = ? and TABLE_NAME = ? and CONSTRAINT_NAME = ?',
                [$database, $table, $constraint]
            );
            return (int) ($row->aggregate ?? 0) > 0;
        } catch (Throwable) {
            return false;
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('batch_allocations')) {
            try {
                Schema::table('batch_allocations', function (Blueprint $table) {
                    $table->dropForeign(['delivery_id']);
                });
            } catch (Throwable) {
                // FK may not exist in a partial/legacy installation.
            }
        }
        Schema::dropIfExists('delivery_items');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('delivery_sequences');
    }
};
