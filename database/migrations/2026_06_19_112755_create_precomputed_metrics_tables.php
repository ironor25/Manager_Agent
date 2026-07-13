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
        Schema::create('dashboard_statistics', function (Blueprint $table) {
            $table->id();
            $table->integer('total_employees')->default(0);
            $table->integer('total_tasks')->default(0);
            $table->integer('completed_tasks')->default(0);
            $table->integer('pending_tasks')->default(0);
            $table->integer('in_progress_tasks')->default(0);
            $table->integer('late_tasks')->default(0);
            $table->json('recent_tasks')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained('employees')->cascadeOnDelete();
            $table->float('task_completion_rate')->default(0);
            $table->float('on_time_completion_rate')->default(0);
            $table->float('attendance_score')->default(0);
            $table->float('git_contribution_score')->default(0);
            $table->float('final_score')->default(0);
            $table->integer('total_tasks')->default(0);
            $table->integer('completed_tasks')->default(0);
            $table->integer('late_tasks')->default(0);
            $table->integer('total_attendance_records')->default(0);
            $table->integer('git_commit_count')->default(0);
            $table->json('recent_commits')->nullable();
            $table->json('commit_chart_data')->nullable();
            $table->timestamps();
        });

        Schema::create('team_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('period'); // '7', '30', 'all'
            $table->string('team_name');
            $table->float('attendance_score')->default(0);
            $table->float('task_completion_score')->default(0);
            $table->float('leadership_score')->default(0);
            $table->float('git_contribution_score')->default(0);
            $table->json('top_performers')->nullable();
            $table->timestamps();

            $table->unique(['period', 'team_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_performance_metrics');
        Schema::dropIfExists('employee_performance_metrics');
        Schema::dropIfExists('dashboard_statistics');
    }
};
