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
        Schema::create('employee_task_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained('employees')->cascadeOnDelete();
            $table->integer('tasks_assigned')->default(0);
            $table->integer('tasks_completed')->default(0);
            $table->integer('tasks_delayed')->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0.00);
            $table->decimal('avg_completion_time_hours', 8, 2)->default(0.00);
            $table->decimal('productivity_score', 5, 2)->default(0.00);
            $table->string('productivity_category')->default('Needs Improvement');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_task_metrics');
    }
};
