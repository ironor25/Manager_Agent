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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('status')->default('active')->after('description');
            $table->string('category')->nullable()->after('status');
            $table->string('team_name')->nullable()->after('category');
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete()->after('team_name');
            $table->timestamp('start_date')->nullable()->after('manager_id');
            $table->timestamp('end_date')->nullable()->after('start_date');
            $table->decimal('budget_hours', 8, 2)->nullable()->after('end_date');
            $table->boolean('is_archived')->default(false)->after('budget_hours');
            
            // Add foreign key constraint for team_name since Team uses name as primary key
            $table->foreign('team_name')->references('name')->on('teams')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropForeign(['team_name']);
            $table->dropColumn([
                'status',
                'category',
                'team_name',
                'manager_id',
                'start_date',
                'end_date',
                'budget_hours',
                'is_archived'
            ]);
        });
    }
};
