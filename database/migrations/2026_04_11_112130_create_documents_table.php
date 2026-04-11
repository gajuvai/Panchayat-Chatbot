<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('document_categories')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->integer('version')->default(1);
            $table->enum('access_level', ['all', 'resident', 'admin'])->default('all');
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'access_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
