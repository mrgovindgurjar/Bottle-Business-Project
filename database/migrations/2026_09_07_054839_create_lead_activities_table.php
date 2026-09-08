<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_activities', function (Blueprint $table) {

            $table->id();

            $table->foreignId('lead_id')
                ->constrained('leads')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('type', 50);

            $table->string('subject', 200)
                ->nullable();

            $table->text('description');

            $table->dateTime('activity_at');

            $table->dateTime('next_followup_at')
                ->nullable();

            $table->json('metadata')
                ->nullable();

            $table->timestamps();

            $table->index([
                'lead_id',
                'activity_at'
            ]);

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
    }
};