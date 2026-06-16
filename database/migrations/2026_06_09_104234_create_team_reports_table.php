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
        Schema::create('team_reports', function (Blueprint $table) {
            $table->id();
            $table->string('team_name');
            $table->integer('efficiency_score')->default(0);
            $table->unsignedBigInteger('top_performer_id')->nullable();
            $table->string('top_performer_name')->nullable();
            $table->text('summary')->nullable();
            $table->json('strengths')->nullable();
            $table->json('weaknesses')->nullable();
            $table->json('recommendations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_reports');
    }
};
