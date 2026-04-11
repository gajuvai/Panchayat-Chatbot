<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('type', 50)->default('other'); // parking, hall, gym, other
            $table->text('description')->nullable();
            $table->integer('capacity')->default(1);       // max concurrent bookings
            $table->boolean('requires_approval')->default(false);
            $table->decimal('fee_per_hour', 8, 2)->default(0);
            $table->string('opening_time', 5)->nullable(); // "08:00"
            $table->string('closing_time', 5)->nullable(); // "22:00"
            $table->json('available_days')->nullable();    // [0,1,2,3,4,5,6] 0=Sun
            $table->boolean('is_active')->default(true);
            $table->string('photo_path', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenities');
    }
};
