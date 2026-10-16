<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_movements')) {
            return;
        }

        Schema::table('inventory_movements', function (Blueprint $table) {
            /*
             * batch_id foreign key is currently using the
             * unique index as its supporting index.
             *
             * Therefore FK must be dropped first.
             */
            try {
                $table->dropForeign(['batch_id']);
            } catch (\Throwable $e) {
                // Foreign key may already be absent.
            }

            /*
             * Now the unique index can safely be removed.
             */
            try {
                $table->dropUnique(
                    'inventory_movements_batch_id_movement_type_unique'
                );
            } catch (\Throwable $e) {
                // Unique index may already be absent.
            }

            /*
             * Allow multiple movements for the same batch/type.
             */
            try {
                $table->index(
                    ['batch_id', 'movement_type'],
                    'inventory_movements_batch_type_index'
                );
            } catch (\Throwable $e) {
                // Index may already exist.
            }

            /*
             * Re-create the foreign key.
             */
            try {
                $table->foreign('batch_id')
                    ->references('id')
                    ->on('batches')
                    ->nullOnDelete();
            } catch (\Throwable $e) {
                // Foreign key may already exist.
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('inventory_movements')) {
            return;
        }

        Schema::table('inventory_movements', function (Blueprint $table) {

            /*
             * Remove normal index first.
             */
            try {
                $table->dropIndex(
                    'inventory_movements_batch_type_index'
                );
            } catch (\Throwable $e) {
                // Index may already be absent.
            }

            /*
             * Restore original unique index.
             *
             * This unique index can also support the existing
             * batch_id foreign key.
             */
            try {
                $table->unique(
                    ['batch_id', 'movement_type'],
                    'inventory_movements_batch_id_movement_type_unique'
                );
            } catch (\Throwable $e) {
                // Unique index may already exist.
            }
        });
    }
};