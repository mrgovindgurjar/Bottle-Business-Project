<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('cap_type', 50)->nullable()->after('material');
            $table->string('label_type', 50)->nullable()->after('cap_type');
            $table->boolean('is_custom_branding')->default(true)->after('unit');
            $table->string('short_description', 300)->nullable()->after('is_custom_branding');
            $table->string('image_path')->nullable()->after('short_description');
            $table->unsignedInteger('sort_order')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'cap_type','label_type','is_custom_branding','short_description','image_path','sort_order',
            ]);
        });
    }
};
