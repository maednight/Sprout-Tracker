<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_transfers', function (Blueprint $table) {
            $table->foreignId('destination_category_id')
                ->nullable()
                ->after('source_category_id')
                ->constrained('categories')
                ->nullOnDelete();
            $table->foreignId('savings_transaction_id')
                ->nullable()
                ->after('account_id')
                ->constrained('transactions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('savings_transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('savings_transaction_id');
            $table->dropConstrainedForeignId('destination_category_id');
        });
    }
};
