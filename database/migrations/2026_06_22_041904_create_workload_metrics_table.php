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
        Schema::create('workload_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->integer('active_tasks')->default(0);
            $table->integer('completed_tasks')->default(0);
            $table->integer('overdue_tasks')->default(0);
            $table->decimal('workload_percentage', 5, 2)->default(0);
            $table->string('status')->default('underutilized'); // underutilized, optimal, busy, overloaded
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workload_metrics');
    }
};
