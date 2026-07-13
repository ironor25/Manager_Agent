<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained('employees')->cascadeOnDelete();
            $table->integer('total_expected_days')->default(0);
            $table->integer('present_days')->default(0);
            $table->integer('absent_days')->default(0);
            $table->integer('late_days')->default(0);
            $table->integer('leave_days')->default(0);
            $table->float('attendance_percentage')->default(0);
            $table->float('punctuality_score')->default(0);
            $table->float('leave_utilization_score')->default(100);
            $table->float('attendance_score')->default(0);
            $table->string('attendance_category')->default('Needs Attention');
            $table->timestamps();
        });

        Schema::create('attendance_metrics_trends', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->integer('present_count')->default(0);
            $table->integer('absent_count')->default(0);
            $table->integer('late_count')->default(0);
            $table->integer('leave_count')->default(0);
            $table->float('avg_attendance_percentage')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_metrics_trends');
        Schema::dropIfExists('attendance_metrics');
    }
};
