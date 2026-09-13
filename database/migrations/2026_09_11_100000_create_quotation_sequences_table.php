<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { if(!Schema::hasTable('quotation_sequences')) Schema::create('quotation_sequences', function(Blueprint $table){$table->unsignedSmallInteger('year')->primary();$table->unsignedBigInteger('next_number')->default(1);$table->timestamps();}); } public function down(): void { Schema::dropIfExists('quotation_sequences'); } };
