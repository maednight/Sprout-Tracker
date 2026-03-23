<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();

            /* Budget Owner */
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            /* Budget Details */
            $table->string('name', 80);
            $table->string('cycle', 20)->default('monthly');
            $table->boolean('is_reused')->default(false);

            /* Budget Period */
            $table->date('period_date');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
