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
        Schema::table('project_batch_evidence_files', function (Blueprint $table) {
            $table->string('commerce_rekon_id')->nullable()->after('project_batch_id')->index();
        });

        Schema::table('project_batch_subfase_statuses', function (Blueprint $table) {
            $table->string('commerce_rekon_id')->nullable()->after('project_batch_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_batch_evidence_files', function (Blueprint $table) {
            $table->dropColumn('commerce_rekon_id');
        });

        Schema::table('project_batch_subfase_statuses', function (Blueprint $table) {
            $table->dropColumn('commerce_rekon_id');
        });
    }
};
