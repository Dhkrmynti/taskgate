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
        // 1. Create Project Batches Table
        if (!Schema::hasTable('project_batches')) {
            Schema::create('project_batches', function (Blueprint $table) {
                $table->string('id')->primary(); // TGIDSP format
                $table->string('project_name');
                $table->string('po_number')->nullable();
                $table->string('customer')->nullable();
                $table->string('branch')->nullable();
                $table->string('fase')->default('ogp_procurement');
                $table->string('boq_file_path')->nullable();
                $table->string('dasar_pekerjaan_file_path')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 2. Add batch_id to Projects Table
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'batch_id')) {
                $table->string('batch_id')->nullable()->after('id');
                $table->foreign('batch_id')->references('id')->on('project_batches')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });
        Schema::dropIfExists('project_batches');
    }
};
