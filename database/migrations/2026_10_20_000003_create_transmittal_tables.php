<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transmittal_header')) {
            Schema::create('transmittal_header', function (Blueprint $table) {
                $table->id();
                $table->string('ref_no')->unique();
                $table->date('transmittal_date');
                $table->string('transmittal_type')->index();
                $table->string('destination');
                $table->string('plate_no')->nullable();
                $table->string('prepared_by');
                $table->string('received_by')->nullable();
                $table->string('noted_by')->nullable();
                $table->string('status')->default('Draft')->index();
                $table->string('attachment')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('transmittal_details')) {
            Schema::create('transmittal_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('outgoing_transmittal_header_id')->constrained('transmittal_header')->cascadeOnDelete();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->text('item_desc');
                $table->string('item_unit')->nullable();
                $table->string('item_serial_no')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transmittal_details');
        Schema::dropIfExists('transmittal_header');
    }
};
