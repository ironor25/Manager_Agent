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
        Schema::table('employees', function (Blueprint $table) {
            $table->index('team');
        });
        
        Schema::table('tasks', function (Blueprint $table) {
            $table->index('employee_id');
            $table->index('status');
            $table->index('completed_at');
            $table->index('deadline_at');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index('employee_id');
            $table->index('login_time');
        });

        Schema::table('performance_reports', function (Blueprint $table) {
            $table->index('employee_id');
            $table->index('created_at');
        });

        Schema::table('github_commits', function (Blueprint $table) {
            $table->index('employee_id');
            $table->index('commit_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['team']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['completed_at']);
            $table->dropIndex(['deadline_at']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['login_time']);
        });

        Schema::table('performance_reports', function (Blueprint $table) {
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('github_commits', function (Blueprint $table) {
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['commit_date']);
        });
    }
};
