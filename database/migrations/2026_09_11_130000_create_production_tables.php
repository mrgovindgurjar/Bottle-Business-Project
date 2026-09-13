<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  if(!Schema::hasTable('production_sequences')) Schema::create('production_sequences',function(Blueprint $t){$t->id();$t->year('year');$t->unsignedBigInteger('last_number')->default(0);$t->timestamps();$t->unique('year');});
  if(!Schema::hasTable('production_orders')) Schema::create('production_orders',function(Blueprint $t){
   $t->id();$t->string('production_number',50)->unique();$t->foreignId('order_id')->constrained('orders')->restrictOnDelete();$t->foreignId('customer_id')->constrained('customers')->restrictOnDelete();$t->unsignedBigInteger('design_id')->nullable();
   $t->decimal('planned_quantity',14,3)->default(0);$t->decimal('produced_quantity',14,3)->default(0);$t->decimal('rejected_quantity',14,3)->default(0);$t->decimal('waste_quantity',14,3)->default(0);
   $t->string('status',30)->default('pending')->index();$t->string('priority',20)->default('normal');$t->date('scheduled_date')->nullable();$t->timestamp('started_at')->nullable();$t->timestamp('completed_at')->nullable();$t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
   $t->string('quality_status',30)->default('pending');$t->text('quality_notes')->nullable();$t->text('notes')->nullable();$t->text('internal_notes')->nullable();$t->foreignId('created_by')->constrained('users')->restrictOnDelete();$t->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamp('cancelled_at')->nullable();$t->timestamps();$t->softDeletes();$t->index(['status','scheduled_date']);$t->index(['customer_id','status']);
  });
  if(!Schema::hasTable('production_order_items')) Schema::create('production_order_items',function(Blueprint $t){$t->id();$t->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();$t->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();$t->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();$t->unsignedBigInteger('design_id')->nullable();$t->text('description');$t->decimal('planned_quantity',14,3);$t->decimal('produced_quantity',14,3)->default(0);$t->decimal('rejected_quantity',14,3)->default(0);$t->string('unit',30)->default('bottle');$t->unsignedInteger('sort_order')->default(0);$t->json('metadata')->nullable();$t->timestamps();});
  if(!Schema::hasTable('production_steps')) Schema::create('production_steps',function(Blueprint $t){$t->id();$t->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();$t->string('code',50);$t->string('name',100);$t->unsignedInteger('sort_order')->default(0);$t->string('status',30)->default('pending');$t->timestamp('started_at')->nullable();$t->timestamp('completed_at')->nullable();$t->text('notes')->nullable();$t->timestamps();$t->unique(['production_order_id','code']);});
 }
 public function down():void{Schema::dropIfExists('production_steps');Schema::dropIfExists('production_order_items');Schema::dropIfExists('production_orders');Schema::dropIfExists('production_sequences');}
};
