<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenity_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 12)->unique();
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('purpose', 255)->nullable();
            $table->integer('guest_count')->default(0);
            $table->enum('status', ['pending','approved','rejected','cancelled','completed'])
                  ->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->decimal('total_fee', 8, 2)->default(0);
            $table->timestamps();

            $table->index(['amenity_id', 'starts_at', 'ends_at', 'status']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenity_bookings');
    }
};
