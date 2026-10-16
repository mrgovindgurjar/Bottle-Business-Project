<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('recurring_plans')) Schema::create('recurring_plans', function(Blueprint $t){
            $t->id(); $t->string('plan_number',50)->unique(); $t->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $t->string('name',150); $t->string('frequency',20)->default('daily'); $t->json('weekdays')->nullable(); $t->unsignedTinyInteger('day_of_month')->nullable();
            $t->date('start_date'); $t->date('end_date')->nullable(); $t->date('next_order_date')->nullable();
            $t->string('status',20)->default('draft')->index(); $t->boolean('auto_generate')->default(true); $t->text('delivery_address')->nullable();
            $t->string('delivery_city',100)->nullable(); $t->string('delivery_state',100)->nullable(); $t->string('delivery_pincode',10)->nullable(); $t->text('notes')->nullable();
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete(); $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete(); $t->timestamp('approved_at')->nullable(); $t->timestamps(); $t->softDeletes();
            $t->index(['customer_id','status']); $t->index(['next_order_date','status']);
        });
        if (!Schema::hasTable('recurring_plan_items')) Schema::create('recurring_plan_items', function(Blueprint $t){
            $t->id(); $t->foreignId('recurring_plan_id')->constrained('recurring_plans')->cascadeOnDelete(); $t->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $t->unsignedBigInteger('design_id')->nullable(); $t->text('description'); $t->decimal('quantity',14,3); $t->string('unit',30)->default('bottle'); $t->decimal('unit_price',14,2)->default(0);
            $t->decimal('discount_amount',14,2)->default(0); $t->decimal('tax_rate',6,2)->default(0); $t->unsignedInteger('sort_order')->default(0); $t->json('metadata')->nullable(); $t->timestamps();
        });
        if (!Schema::hasTable('daily_orders')) Schema::create('daily_orders', function(Blueprint $t){
            $t->id(); $t->string('daily_order_number',50)->unique(); $t->foreignId('recurring_plan_id')->constrained('recurring_plans')->restrictOnDelete();
            $t->foreignId('customer_id')->constrained('customers')->restrictOnDelete(); $t->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $t->date('scheduled_date'); $t->string('status',20)->default('pending')->index(); $t->decimal('grand_total',14,2)->default(0); $t->text('delivery_address')->nullable();
            $t->string('delivery_city',100)->nullable(); $t->string('delivery_state',100)->nullable(); $t->string('delivery_pincode',10)->nullable(); $t->timestamp('generated_at')->nullable();
            $t->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete(); $t->text('notes')->nullable(); $t->timestamps();
            $t->unique(['recurring_plan_id','scheduled_date']); $t->index(['customer_id','scheduled_date','status']);
        });
        if (!Schema::hasTable('daily_order_items')) Schema::create('daily_order_items', function(Blueprint $t){
            $t->id(); $t->foreignId('daily_order_id')->constrained('daily_orders')->cascadeOnDelete(); $t->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $t->unsignedBigInteger('design_id')->nullable(); $t->text('description'); $t->decimal('quantity',14,3); $t->string('unit',30)->default('bottle'); $t->decimal('unit_price',14,2)->default(0);
            $t->decimal('discount_amount',14,2)->default(0); $t->decimal('tax_rate',6,2)->default(0); $t->decimal('tax_amount',14,2)->default(0); $t->decimal('line_total',14,2)->default(0); $t->unsignedInteger('sort_order')->default(0); $t->timestamps();
        });
        if (!Schema::hasTable('recurring_plan_sequences')) Schema::create('recurring_plan_sequences', function(Blueprint $t){$t->unsignedSmallInteger('year')->primary();$t->unsignedBigInteger('next_number')->default(1);$t->timestamps();});
        if (!Schema::hasTable('daily_order_sequences')) Schema::create('daily_order_sequences', function(Blueprint $t){$t->unsignedSmallInteger('year')->primary();$t->unsignedBigInteger('next_number')->default(1);$t->timestamps();});
    }
    public function down(): void { Schema::dropIfExists('daily_order_items'); Schema::dropIfExists('daily_orders'); Schema::dropIfExists('recurring_plan_items'); Schema::dropIfExists('recurring_plans'); Schema::dropIfExists('daily_order_sequences'); Schema::dropIfExists('recurring_plan_sequences'); }
};
