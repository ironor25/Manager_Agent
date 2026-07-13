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
        Schema::create('employee_daily_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            
            // Raw metrics
            $table->integer('total_tasks')->default(0);
            $table->integer('completed_tasks')->default(0);
            $table->integer('on_time_tasks')->default(0);
            $table->integer('total_attendances')->default(0);
            $table->integer('present_days')->default(0);
            $table->integer('git_commit_count')->default(0);
            $table->integer('project_count')->default(0);
            
            // Computed Daily Scores
            $table->float('task_completion_score')->default(0);
            $table->float('task_quality_score')->default(0);
            $table->float('attendance_score')->default(0);
            $table->float('gitlab_score')->default(0);
            $table->float('overall_score')->default(0);
            
            $table->timestamps();
            
            $table->unique(['employee_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_daily_metrics');
    }
};
