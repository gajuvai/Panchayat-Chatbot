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
        Schema::create('security_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->enum('incident_type', ['theft', 'trespass', 'vandalism', 'suspicious_activity', 'emergency', 'other']);
            $table->text('description');
            $table->string('location', 255);
            $table->dateTime('occurred_at');
            $table->enum('status', ['active', 'investigating', 'resolved'])->default('active');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_incidents');
    }
};
