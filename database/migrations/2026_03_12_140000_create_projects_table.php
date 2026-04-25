<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('project_name');
            $table->string('pid_proactive')->nullable();
            $table->string('wbs_sap')->nullable();
            $table->string('customer')->nullable();
            $table->string('portofolio')->nullable();
            $table->string('program')->nullable();
            $table->string('jenis_eksekusi')->nullable();
            $table->string('witel')->nullable();
            $table->string('pm_project')->nullable();
            $table->string('waspang')->nullable();
            $table->string('evidence_dasar_path')->nullable();
            $table->string('boq_file_path')->nullable();
            $table->date('start_project')->nullable();
            $table->date('end_project')->nullable();
            $table->timestamps();

            $table->index('project_name');
            $table->index('pid_proactive');
            $table->index('customer');
            $table->index('program');
            $table->index('witel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
