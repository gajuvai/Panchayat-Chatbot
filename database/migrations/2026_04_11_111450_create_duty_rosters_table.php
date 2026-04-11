<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duty_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->enum('type', ['weekly_duty','event_volunteer','committee','other'])->default('weekly_duty');
            $table->date('roster_date');
            $table->string('shift_start', 5);   // "08:00"
            $table->string('shift_end', 5);     // "12:00"
            $table->unsignedInteger('slots_required')->default(1);
            $table->boolean('is_open_signup')->default(false);
            $table->timestamps();

            $table->index(['roster_date', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duty_rosters');
    }
};
