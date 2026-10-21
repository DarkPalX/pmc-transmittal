<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('transmittal_details', 'item_qty')) {
            Schema::table('transmittal_details', function (Blueprint $table) {
                $table->decimal('item_qty', 12, 2)->default(1);
            });
        }
    }

    public function down(): void
    {
        Schema::table('transmittal_details', function (Blueprint $table) {
            $table->dropColumn('item_qty');
        });
    }
};
