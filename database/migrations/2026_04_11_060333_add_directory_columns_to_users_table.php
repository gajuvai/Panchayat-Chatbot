<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_listed_in_directory')->default(false)->after('is_active');
            $table->string('directory_display_name', 100)->nullable()->after('is_listed_in_directory');
            $table->text('bio')->nullable()->after('directory_display_name');
            $table->string('whatsapp', 15)->nullable()->after('bio');
            $table->json('interests')->nullable()->after('whatsapp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_listed_in_directory',
                'directory_display_name',
                'bio',
                'whatsapp',
                'interests',
            ]);
        });
    }
};
