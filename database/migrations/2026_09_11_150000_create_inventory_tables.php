<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if(!Schema::hasTable('inventory_items')) Schema::create('inventory_items',function(Blueprint $t){
            $t->id();
            $t->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $t->string('name',200); $t->string('sku',100)->unique();
            $t->string('category',40)->default('finished_goods')->index();
            $t->string('unit',30)->default('bottle'); $t->string('location',100)->nullable()->index();
            $t->decimal('on_hand',14,3)->default(0); $t->decimal('reserved',14,3)->default(0);
            $t->decimal('reorder_level',14,3)->default(0); $t->decimal('reorder_quantity',14,3)->default(0);
            $t->decimal('average_cost',14,2)->default(0); $t->string('status',20)->default('active')->index();
            $t->text('notes')->nullable(); $t->timestamps(); $t->softDeletes();
            $t->index(['category','status']);
        });
        if(!Schema::hasTable('inventory_movements')) Schema::create('inventory_movements',function(Blueprint $t){
            $t->id(); $t->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $t->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $t->string('movement_type',40)->index(); $t->decimal('quantity',14,3); $t->decimal('unit_cost',14,2)->nullable();
            $t->string('reference_type',60)->nullable()->index(); $t->unsignedBigInteger('reference_id')->nullable()->index();
            $t->timestamp('movement_date')->useCurrent()->index();
            $t->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->text('notes')->nullable(); $t->json('metadata')->nullable(); $t->timestamps();
            $t->index(['inventory_item_id','movement_date']);
            $t->unique(['batch_id','movement_type']);
        });
    }
    public function down(): void { Schema::dropIfExists('inventory_movements'); Schema::dropIfExists('inventory_items'); }
};
