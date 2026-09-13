<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('payments', function(Blueprint $t){
   $t->id(); $t->string('payment_number',40)->unique(); $t->enum('direction',['received','paid']);
   $t->string('payment_type',40); $t->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
   $t->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
   $t->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
   $t->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
   if(Schema::hasTable('invoices')) $t->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete(); else $t->unsignedBigInteger('invoice_id')->nullable();
   $t->date('payment_date'); $t->decimal('amount',15,2); $t->string('method',30); $t->string('reference_number')->nullable();
   $t->string('bank_name')->nullable(); $t->date('transaction_date')->nullable(); $t->text('notes')->nullable(); $t->string('status',30)->default('completed');
   $t->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete(); $t->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
   $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete(); $t->timestamp('approved_at')->nullable();
   $t->string('receipt_number',40)->nullable()->unique(); $t->foreignId('refund_of_payment_id')->nullable()->constrained('payments')->nullOnDelete(); $t->json('metadata')->nullable(); $t->timestamps(); $t->softDeletes();
   $t->index(['direction','status']); $t->index(['customer_id','payment_date']); $t->index(['supplier_id','payment_date']);
  });
  Schema::create('payment_allocations', function(Blueprint $t){
   $t->id(); $t->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
   if(Schema::hasTable('invoices')) $t->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete(); else $t->unsignedBigInteger('invoice_id')->nullable(); $t->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete(); $t->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
   $t->decimal('allocated_amount',15,2); $t->date('allocation_date'); $t->text('notes')->nullable(); $t->timestamp('reversed_at')->nullable(); $t->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete(); $t->text('reversal_reason')->nullable(); $t->timestamps();
   $t->index(['invoice_id','reversed_at']); $t->index(['order_id','reversed_at']); $t->index(['purchase_id','reversed_at']);
  });
 }
 public function down(): void { Schema::dropIfExists('payment_allocations'); Schema::dropIfExists('payments'); }
};
