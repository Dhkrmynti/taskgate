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
        Schema::create('warehouse_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('original_file_name');
            $table->integer('total_rows')->default(0);
            $table->timestamp('imported_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('warehouse_tab_schemas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('warehouse_import_batches')->cascadeOnDelete();
            $table->string('tab_key')->index();
            $table->string('tab_label');
            $table->integer('row_count')->default(0);
            $table->json('headers');
            $table->timestamps();
        });

        Schema::create('warehouse_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('warehouse_import_batches')->cascadeOnDelete();
            $table->string('tab_key')->index();
            $table->integer('row_number');
            $table->string('project_id')->nullable()->index(); // Specifically for "PID"
            $table->json('data');
            $table->text('search_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_records');
        Schema::dropIfExists('warehouse_tab_schemas');
        Schema::dropIfExists('warehouse_import_batches');
    }
};
