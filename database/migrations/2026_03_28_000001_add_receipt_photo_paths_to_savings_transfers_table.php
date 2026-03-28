<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_transfers', function (Blueprint $table) {
            $table->string('receipt_photo_path')
                ->nullable()
                ->after('description');
            $table->json('receipt_photo_paths')
                ->nullable()
                ->after('receipt_photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('savings_transfers', function (Blueprint $table) {
            $table->dropColumn([
                'receipt_photo_path',
                'receipt_photo_paths',
            ]);
        });
    }
};
