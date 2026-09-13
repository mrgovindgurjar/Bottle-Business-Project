<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('income_categories')) Schema::create('income_categories', function(Blueprint $t){ $t->id(); $t->string('name'); $t->string('slug')->unique(); $t->string('status',20)->default('active'); $t->unsignedInteger('sort_order')->default(0); $t->timestamps(); });
        if (!Schema::hasTable('expense_categories')) Schema::create('expense_categories', function(Blueprint $t){ $t->id(); $t->string('name'); $t->string('slug')->unique(); $t->string('status',20)->default('active'); $t->unsignedInteger('sort_order')->default(0); $t->timestamps(); });
        if (!Schema::hasTable('finance_sequences')) Schema::create('finance_sequences', function(Blueprint $t){ $t->id(); $t->string('sequence_key')->unique(); $t->unsignedBigInteger('current_number')->default(0); $t->timestamps(); });
        if (!Schema::hasTable('incomes')) Schema::create('incomes', function(Blueprint $t){
            $t->id(); $t->string('income_number',40)->unique(); $t->foreignId('category_id')->constrained('income_categories')->restrictOnDelete(); $t->date('income_date'); $t->decimal('amount',15,2); $t->string('payment_method',30); $t->string('reference_number')->nullable();
            $t->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete(); $t->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete(); $t->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete(); $t->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $t->string('source_type',30)->default('manual'); $t->string('description'); $t->text('notes')->nullable(); $t->string('status',20)->default('posted'); $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); $t->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete(); $t->timestamp('cancelled_at')->nullable(); $t->text('cancellation_reason')->nullable(); $t->timestamps(); $t->softDeletes(); $t->index(['income_date','status']); $t->index(['customer_id','income_date']);
        });
        if (!Schema::hasTable('expenses')) Schema::create('expenses', function(Blueprint $t){
            $t->id(); $t->string('expense_number',40)->unique(); $t->foreignId('category_id')->constrained('expense_categories')->restrictOnDelete(); $t->date('expense_date'); $t->decimal('amount',15,2); $t->string('payment_method',30); $t->string('reference_number')->nullable();
            $t->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete(); $t->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete(); $t->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $t->string('source_type',30)->default('manual'); $t->string('description'); $t->text('notes')->nullable(); $t->string('status',20)->default('posted'); $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); $t->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete(); $t->timestamp('cancelled_at')->nullable(); $t->text('cancellation_reason')->nullable(); $t->timestamps(); $t->softDeletes(); $t->index(['expense_date','status']); $t->index(['supplier_id','expense_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('expenses'); Schema::dropIfExists('incomes'); Schema::dropIfExists('finance_sequences'); Schema::dropIfExists('expense_categories'); Schema::dropIfExists('income_categories'); }
};
