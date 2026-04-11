<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained('users')->cascadeOnDelete();
            $table->string('visitor_name', 100);
            $table->string('visitor_phone', 15)->nullable();
            $table->string('vehicle_number', 20)->nullable();
            $table->string('purpose', 255)->nullable();
            $table->date('expected_date');
            $table->time('expected_from')->nullable();
            $table->time('expected_to')->nullable();
            $table->string('pass_code', 10)->unique();
            $table->enum('status', ['pending', 'approved', 'checked_in', 'checked_out', 'expired', 'cancelled'])
                  ->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['expected_date', 'status']);
            $table->index('resident_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_passes');
    }
};
