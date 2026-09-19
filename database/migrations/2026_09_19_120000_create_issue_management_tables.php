<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('issue_categories')) {
            Schema::create('issue_categories', function(Blueprint $table){
                $table->id(); $table->string('name',100); $table->string('slug',120)->unique(); $table->text('description')->nullable();
                $table->boolean('is_active')->default(true); $table->unsignedInteger('sort_order')->default(0); $table->timestamps();
                $table->index(['is_active','sort_order']);
            });
        }
        if (!Schema::hasTable('issues')) {
            Schema::create('issues', function(Blueprint $table){
                $table->id(); $table->string('issue_number',40)->unique();
                $table->foreignId('category_id')->nullable()->constrained('issue_categories')->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->foreignId('delivery_id')->nullable()->constrained('deliveries')->nullOnDelete();
                $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
                $table->foreignId('assigned_to')->nullable()->constrained('staff')->nullOnDelete();
                $table->string('priority',20)->default('normal'); $table->string('status',30)->default('open');
                $table->string('title',200); $table->longText('description'); $table->string('source',50)->nullable();
                $table->dateTime('reported_at')->nullable(); $table->date('due_date')->nullable();
                $table->dateTime('resolved_at')->nullable(); $table->dateTime('closed_at')->nullable();
                $table->longText('resolution')->nullable(); $table->boolean('customer_visible')->default(true); $table->json('attachments')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps(); $table->softDeletes();
                $table->index(['status','priority']); $table->index(['due_date','status']); $table->index(['customer_id','status']); $table->index(['assigned_to','status']);
            });
        }
        if (!Schema::hasTable('issue_comments')) {
            Schema::create('issue_comments', function(Blueprint $table){
                $table->id(); $table->foreignId('issue_id')->constrained('issues')->cascadeOnDelete(); $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->longText('comment'); $table->boolean('is_internal')->default(true); $table->json('attachments')->nullable(); $table->timestamps();
                $table->index(['issue_id','created_at']);
            });
        }
        if (!Schema::hasTable('issue_activities')) {
            Schema::create('issue_activities', function(Blueprint $table){
                $table->id(); $table->foreignId('issue_id')->constrained('issues')->cascadeOnDelete(); $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('event_type',40); $table->string('old_status',30)->nullable(); $table->string('new_status',30)->nullable();
                $table->unsignedBigInteger('old_assignee')->nullable(); $table->unsignedBigInteger('new_assignee')->nullable(); $table->text('message')->nullable(); $table->json('metadata')->nullable(); $table->timestamps();
                $table->index(['issue_id','created_at']);
            });
        }
    }
    public function down(): void { Schema::dropIfExists('issue_activities'); Schema::dropIfExists('issue_comments'); Schema::dropIfExists('issues'); Schema::dropIfExists('issue_categories'); }
};
