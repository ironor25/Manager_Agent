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
        Schema::create('teams', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Migrate existing teams from the employees table
        $existingTeams = DB::table('employees')
            ->whereNotNull('team')
            ->distinct()
            ->pluck('team');

        foreach ($existingTeams as $teamName) {
            DB::table('teams')->insert([
                'name' => $teamName,
                'description' => "This is the $teamName team.",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
