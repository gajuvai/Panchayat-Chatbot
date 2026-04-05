<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate votes before adding constraint
        \DB::statement('
            DELETE FROM poll_votes
            WHERE id NOT IN (
                SELECT MIN(id) FROM poll_votes GROUP BY poll_id, user_id
            )
            AND user_id IS NOT NULL
        ');

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->unique(['poll_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropUnique(['poll_id', 'user_id']);
        });
    }
};
