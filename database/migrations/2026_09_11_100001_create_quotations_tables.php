<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  if(!Schema::hasTable('quotations')) Schema::create('quotations', function(Blueprint $table){
   $table->id(); $table->string('quotation_number',50)->unique(); $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete(); $table->unsignedBigInteger('design_id')->nullable(); $table->date('quotation_date'); $table->date('valid_until')->nullable(); $table->string('status',30)->default('draft')->index(); $table->decimal('subtotal',14,2)->default(0); $table->string('discount_type',20)->nullable(); $table->decimal('discount_value',14,2)->default(0); $table->decimal('discount_amount',14,2)->default(0); $table->string('tax_type',20)->default('none'); $table->decimal('tax_rate',6,2)->default(0); $table->decimal('tax_amount',14,2)->default(0); $table->decimal('shipping_amount',14,2)->default(0); $table->decimal('other_amount',14,2)->default(0); $table->decimal('grand_total',14,2)->default(0); $table->text('notes')->nullable(); $table->text('terms_conditions')->nullable(); $table->foreignId('created_by')->constrained('users')->restrictOnDelete(); $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('approved_at')->nullable(); $table->timestamp('sent_at')->nullable(); $table->timestamp('converted_to_order_at')->nullable(); $table->timestamps(); $table->softDeletes(); $table->index(['customer_id','status']);
  });
  if(!Schema::hasTable('quotation_items')) Schema::create('quotation_items', function(Blueprint $table){
   $table->id(); $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete(); $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete(); $table->unsignedBigInteger('design_id')->nullable(); $table->text('description'); $table->decimal('quantity',14,3); $table->string('unit',30)->default('bottle'); $table->decimal('unit_price',14,2); $table->string('discount_type',20)->nullable(); $table->decimal('discount_value',14,2)->default(0); $table->decimal('discount_amount',14,2)->default(0); $table->decimal('tax_rate',6,2)->default(0); $table->decimal('tax_amount',14,2)->default(0); $table->decimal('line_total',14,2)->default(0); $table->unsignedInteger('sort_order')->default(0); $table->json('metadata')->nullable(); $table->timestamps();
  });
 }
 public function down(): void { Schema::dropIfExists('quotation_items'); Schema::dropIfExists('quotations'); }
};
