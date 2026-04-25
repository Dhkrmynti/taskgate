<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khs_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('original_file_name');
            $table->unsignedInteger('total_rows')->default(0);
            $table->timestamp('imported_at');
            $table->timestamps();
        });

        Schema::create('khs_tab_schemas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('khs_import_batches')->cascadeOnDelete();
            $table->string('tab_key');
            $table->string('tab_label');
            $table->unsignedInteger('row_count')->default(0);
            $table->json('headers');
            $table->timestamps();

            $table->unique(['batch_id', 'tab_key']);
            $table->index('tab_key');
        });

        Schema::create('khs_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('khs_import_batches')->cascadeOnDelete();
            $table->string('tab_key');
            $table->unsignedInteger('row_number');
            $table->json('data');
            $table->text('search_text')->nullable();
            $table->timestamps();

            $table->index(['tab_key', 'row_number']);
            $table->index(['batch_id', 'tab_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khs_records');
        Schema::dropIfExists('khs_tab_schemas');
        Schema::dropIfExists('khs_import_batches');
    }
};
