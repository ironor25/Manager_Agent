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
        Schema::table('github_commits', function (Blueprint $table) {
            $table->string('source')->default('github');
            $table->string('event_type')->default('push');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('github_commits', function (Blueprint $table) {
            $table->dropColumn(['source', 'event_type']);
        });
    }
};
