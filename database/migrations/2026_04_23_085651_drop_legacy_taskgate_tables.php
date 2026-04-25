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
        Schema::dropIfExists('project_evidence_files');
        Schema::dropIfExists('project_subfase_statuses');
        Schema::dropIfExists('project_batch_evidence_files');
        Schema::dropIfExists('project_batch_subfase_statuses');
        Schema::dropIfExists('project_evidences');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
