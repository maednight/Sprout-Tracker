<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('income_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->dateTime('transferred_at');
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'transferred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_transfers');
    }
};
