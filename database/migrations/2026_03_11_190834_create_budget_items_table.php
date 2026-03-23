<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();

            /* Budget Reference */
            $table->foreignId('budget_id')
                ->constrained()
                ->cascadeOnDelete();

            /* Category Reference */
            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /* Stored Category Snapshot */
            $table->string('category_name');

            /* Allocated Amount */
            $table->decimal('allocated_amount', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_items');
    }
};
