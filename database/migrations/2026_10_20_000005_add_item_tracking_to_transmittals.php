<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transmittal_details', function (Blueprint $table) {
            if (! Schema::hasColumn('transmittal_details', 'par_no')) $table->string('par_no')->nullable();
            if (! Schema::hasColumn('transmittal_details', 'attachment')) $table->string('attachment')->nullable();
        });

        Schema::table('items', function (Blueprint $table) {
            if (! Schema::hasColumn('items', 'transmittal_date')) $table->date('transmittal_date')->nullable();
            if (! Schema::hasColumn('items', 'par_no')) $table->string('par_no')->nullable();
            if (! Schema::hasColumn('items', 'attachment')) $table->string('attachment')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('transmittal_details', function (Blueprint $table) {
            $table->dropColumn(['par_no', 'attachment']);
        });
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['transmittal_date', 'par_no', 'attachment']);
        });
    }
};
