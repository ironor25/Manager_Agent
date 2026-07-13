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
        Schema::create('workload_trends', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('average_workload', 5, 2)->default(0);
            $table->integer('underutilized_count')->default(0);
            $table->integer('optimal_count')->default(0);
            $table->integer('busy_count')->default(0);
            $table->integer('overloaded_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workload_trends');
    }
};
