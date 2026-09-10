<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('design_requests', function (Blueprint $table) {
            $table->id();
            $table->string('design_code', 40)->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 200);
            $table->string('design_type', 40)->default('restaurant');
            $table->string('status', 40)->default('draft');
            $table->text('brief')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('design_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('design_request_id')->constrained('design_requests')->cascadeOnDelete();
            $table->unsignedInteger('version_no');
            $table->string('name', 150)->nullable();
            $table->json('design_data');
            $table->string('front_artwork_path')->nullable();
            $table->string('back_artwork_path')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('preview_path')->nullable();
            $table->string('status', 40)->default('draft');
            $table->text('change_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->unique(['design_request_id', 'version_no']);
        });

        Schema::create('design_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('design_version_id')->constrained('design_versions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 30)->default('comment');
            $table->text('comment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_comments');
        Schema::dropIfExists('design_versions');
        Schema::dropIfExists('design_requests');
    }
};
