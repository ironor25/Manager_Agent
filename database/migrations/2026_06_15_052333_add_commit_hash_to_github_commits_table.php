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
            $table->string('commit_hash', 40)->nullable()->after('repo_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('github_commits', function (Blueprint $table) {
            $table->dropColumn('commit_hash');
        });
    }
};
