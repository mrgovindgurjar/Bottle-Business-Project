<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { if(Schema::hasTable('orders') && !Schema::hasColumn('orders','quotation_id')) Schema::table('orders',function(Blueprint $table){$table->foreignId('quotation_id')->nullable()->after('id')->constrained('quotations')->nullOnDelete();}); } public function down(): void { if(Schema::hasTable('orders') && Schema::hasColumn('orders','quotation_id')) Schema::table('orders',function(Blueprint $table){$table->dropForeign(['quotation_id']);$table->dropColumn('quotation_id');}); } };
