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
        Schema::create('project_batch_boq_details', function (Blueprint $table) {
            $table->id();
            $table->string('project_batch_id');
            $table->foreign('project_batch_id')->references('id')->on('project_batches')->cascadeOnDelete();
            $table->string('designator');
            $table->text('description');
            $table->unsignedInteger('volume_planning')->default(0);
            $table->decimal('price_planning', 15, 2)->default(0);
            $table->unsignedInteger('volume_pemenuhan')->default(0);
            $table->unsignedInteger('volume_aktual')->default(0);
            $table->decimal('price_aktual', 15, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_batch_evidence_files', function (Blueprint $table) {
            $table->id();
            $table->string('project_batch_id');
            $table->foreign('project_batch_id')->references('id')->on('project_batches')->cascadeOnDelete();
            $table->string('type');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_extension');
            $table->unsignedBigInteger('file_size');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_batch_subfase_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('project_batch_id');
            $table->foreign('project_batch_id')->references('id')->on('project_batches')->cascadeOnDelete();
            $table->string('subfase_key');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_batch_detail_tables');
    }
};
