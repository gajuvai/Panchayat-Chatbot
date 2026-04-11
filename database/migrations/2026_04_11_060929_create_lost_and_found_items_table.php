<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lost_and_found_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['lost', 'found']);
            $table->string('title', 100);
            $table->text('description');
            $table->string('location', 255)->nullable();
            $table->date('date_occurred');
            $table->string('contact_info', 255)->nullable();
            $table->string('photo_path', 500)->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_resolved', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lost_and_found_items');
    }
};
