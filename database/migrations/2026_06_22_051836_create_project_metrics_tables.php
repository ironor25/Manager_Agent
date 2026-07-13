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
        Schema::create('project_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained('projects')->cascadeOnDelete();
            $table->integer('total_tasks')->default(0);
            $table->integer('completed_tasks')->default(0);
            $table->integer('overdue_tasks')->default(0);
            $table->decimal('total_estimated_hours', 10, 2)->default(0);
            $table->decimal('total_actual_hours', 10, 2)->default(0);
            $table->float('completion_percentage')->default(0);
            $table->float('health_score')->default(100);
            $table->string('health_status')->default('On Track'); // On Track, At Risk, Critical
            $table->timestamps();
        });

        Schema::create('project_metrics_trends', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->integer('active_projects')->default(0);
            $table->integer('at_risk_projects')->default(0);
            $table->integer('critical_projects')->default(0);
            $table->float('avg_completion_percentage')->default(0);
            $table->timestamps();
            
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_metrics_trends');
        Schema::dropIfExists('project_metrics');
    }
};
