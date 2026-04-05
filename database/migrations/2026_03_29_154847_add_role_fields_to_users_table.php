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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete()->after('id');
            $table->string('flat_number', 20)->nullable()->after('email');
            $table->string('block', 10)->nullable()->after('flat_number');
            $table->string('phone', 15)->nullable()->after('block');
            $table->string('profile_photo', 255)->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('profile_photo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'flat_number', 'block', 'phone', 'profile_photo', 'is_active']);
        });
    }
};
