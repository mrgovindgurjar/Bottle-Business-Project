<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('staff')) {
            Schema::table('staff', function (Blueprint $table) {
                if (!Schema::hasColumn('staff','user_id')) $table->foreignId('user_id')->nullable()->after('employee_code')->constrained('users')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('salary_payments')) {
            Schema::create('salary_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('staff_id')->constrained('staff')->restrictOnDelete();
                $table->unsignedSmallInteger('salary_year');
                $table->unsignedTinyInteger('salary_month');
                $table->date('payment_date')->nullable();
                $table->decimal('base_salary',14,2)->default(0);
                $table->decimal('working_days',6,2)->default(0);
                $table->decimal('present_days',6,2)->default(0);
                $table->decimal('half_days',6,2)->default(0);
                $table->decimal('leave_days',6,2)->default(0);
                $table->decimal('absent_days',6,2)->default(0);
                $table->decimal('holiday_days',6,2)->default(0);
                $table->decimal('paid_leave_days',6,2)->default(0);
                $table->decimal('payable_days',6,2)->default(0);
                $table->decimal('per_day_salary',14,2)->default(0);
                $table->decimal('attendance_earned',14,2)->default(0);
                $table->decimal('bonus',14,2)->default(0);
                $table->decimal('deduction',14,2)->default(0);
                $table->decimal('net_salary',14,2)->default(0);
                $table->string('payment_method',30)->nullable();
                $table->string('reference_number',100)->nullable();
                $table->string('status',30)->default('draft');
                $table->text('notes')->nullable();
                $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['staff_id','salary_year','salary_month']);
                $table->index(['salary_year','salary_month','status']);
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
