<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate votes before adding constraint.
        // The subquery is wrapped in a derived table alias so MySQL does not
        // complain about referencing the target table in the FROM clause
        // (SQLSTATE HY000 / error 1093).
        \DB::statement('
            DELETE FROM poll_votes
            WHERE id NOT IN (
                SELECT id FROM (
                    SELECT MIN(id) AS id FROM poll_votes GROUP BY poll_id, user_id
                ) AS keep_ids
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
