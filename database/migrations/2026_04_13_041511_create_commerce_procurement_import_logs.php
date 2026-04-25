<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commerce_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('original_file_name');
            $table->integer('total_rows')->default(0);
            $table->timestamp('imported_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('procurement_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('original_file_name');
            $table->integer('total_rows')->default(0);
            $table->timestamp('imported_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_import_batches');
        Schema::dropIfExists('commerce_import_batches');
    }
};
