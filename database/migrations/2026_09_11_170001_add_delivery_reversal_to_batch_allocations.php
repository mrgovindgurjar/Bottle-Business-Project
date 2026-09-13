<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('batch_allocations')) return;
        Schema::table('batch_allocations', function (Blueprint $table) {
            if (!Schema::hasColumn('batch_allocations','reversed_at')) $table->timestamp('reversed_at')->nullable()->index();
            if (!Schema::hasColumn('batch_allocations','reversed_by')) $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            if (!Schema::hasColumn('batch_allocations','reversal_reason')) $table->text('reversal_reason')->nullable();
        });
    }
    public function down(): void
    {
        if (!Schema::hasTable('batch_allocations')) return;
        Schema::table('batch_allocations', function (Blueprint $table) {
            try { $table->dropForeign(['reversed_by']); } catch (Throwable) {}
            foreach (['reversed_at','reversed_by','reversal_reason'] as $column) if (Schema::hasColumn('batch_allocations',$column)) $table->dropColumn($column);
        });
    }
};
