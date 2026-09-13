<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('staff')) {
            Schema::create('staff', function (Blueprint $table) {
                $table->id();
                $table->string('employee_code',50)->unique();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('first_name',100); $table->string('last_name',100)->nullable();
                $table->string('email',150)->nullable()->unique(); $table->string('mobile',30)->nullable();
                $table->date('date_of_birth')->nullable(); $table->string('gender',20)->nullable();
                $table->string('designation',100)->nullable(); $table->string('department',100)->nullable();
                $table->date('joining_date'); $table->string('employment_type',30)->default('full_time');
                $table->decimal('salary',14,2)->nullable(); $table->text('address')->nullable();
                $table->string('city',100)->nullable(); $table->string('state',100)->nullable(); $table->string('pincode',20)->nullable();
                $table->string('emergency_contact_name',150)->nullable(); $table->string('emergency_contact_mobile',30)->nullable();
                $table->string('status',30)->default('active'); $table->text('notes')->nullable();
                $table->timestamps(); $table->softDeletes();
                $table->index(['department','status']);
            });
        }
        if (!Schema::hasTable('staff_attendances')) {
            Schema::create('staff_attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
                $table->date('attendance_date');
                $table->string('status',30)->default('present');
                $table->time('check_in')->nullable(); $table->time('check_out')->nullable();
                $table->unsignedInteger('late_minutes')->default(0); $table->unsignedInteger('work_minutes')->default(0); $table->unsignedInteger('overtime_minutes')->default(0);
                $table->text('remarks')->nullable();
                $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['staff_id','attendance_date']);
                $table->index(['attendance_date','status']);
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('staff_attendances'); Schema::dropIfExists('staff');
    }
};
