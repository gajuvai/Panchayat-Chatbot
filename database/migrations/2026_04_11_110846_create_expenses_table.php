<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('category', [
                'maintenance','security','utilities','events','staff_salary',
                'cleaning','landscaping','administrative','emergency','other'
            ])->default('other');
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->string('vendor', 100)->nullable();
            $table->string('invoice_number', 50)->nullable();
            $table->string('receipt_path', 500)->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();

            $table->index(['category', 'expense_date']);
            $table->index('expense_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
