<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transmittal_header')) {
            DB::table('transmittal_header')->whereIn('status', ['Draft', 'For Receiving'])->update(['status' => 'Saved']);
            DB::table('transmittal_header')->whereIn('status', ['Received', 'Closed'])->update(['status' => 'Transmitted']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transmittal_header')) {
            DB::table('transmittal_header')->where('status', 'Saved')->update(['status' => 'Draft']);
            DB::table('transmittal_header')->where('status', 'Transmitted')->update(['status' => 'Received']);
        }
    }
};
