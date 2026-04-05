<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->index('status');
            $table->index('priority');
            $table->index(['status', 'priority']);  // composite for dashboard queries
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->index('is_published');
            $table->index('published_at');
            $table->index('expires_at');
        });

        Schema::table('polls', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('ends_at');
            $table->index(['is_active', 'ends_at']);  // composite for isActiveNow()
        });

        Schema::table('event_rsvps', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('forum_replies', function (Blueprint $table) {
            $table->index('parent_id');
        });

        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('complaints',   fn($t) => $t->dropIndex(['status']) );
        Schema::table('announcements',fn($t) => $t->dropIndex(['is_published']) );
        Schema::table('polls',        fn($t) => $t->dropIndex(['is_active']) );
        Schema::table('event_rsvps',  fn($t) => $t->dropIndex(['status']) );
        Schema::table('forum_replies',fn($t) => $t->dropIndex(['parent_id']) );
        Schema::table('chat_sessions',fn($t) => $t->dropIndex(['is_active']) );
    }
};
