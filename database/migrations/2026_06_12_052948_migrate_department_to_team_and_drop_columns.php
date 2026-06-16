<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Copy department data to team
        DB::statement('UPDATE employees SET team = department WHERE department IS NOT NULL');
        
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['department', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('department')->nullable();
            $table->string('role')->nullable();
        });
        
        // Copy team data back to department
        DB::statement('UPDATE employees SET department = team WHERE team IS NOT NULL');
    }
};
