<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate votes before adding constraint
        // Wrapped in a subquery alias to satisfy MySQL's restriction on
        // referencing the target table in a DELETE subquery
        \DB::statement('
            DELETE FROM poll_votes
            WHERE id NOT IN (
                SELECT min_id FROM (
                    SELECT MIN(id) AS min_id FROM poll_votes GROUP BY poll_id, user_id
                ) AS tmp
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
