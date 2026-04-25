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
        Schema::create('finance_tab_schemas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('finance_import_batches')->cascadeOnDelete();
            $table->string('tab_key');
            $table->string('tab_label');
            $table->integer('row_count')->default(0);
            $table->json('headers')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_tab_schemas');
    }
};
