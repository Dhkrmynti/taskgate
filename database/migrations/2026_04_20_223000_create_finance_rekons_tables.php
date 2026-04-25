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
        Schema::create('finance_rekons', function (Blueprint $table) {
            $table->string('id')->primary(); // TGIDRF-YYYYMMDDXXX
            $table->string('apm_number')->nullable();
            $table->string('evidence_path')->nullable();
            $table->string('boq_file_path')->nullable();
            
            $table->decimal('total_jasa_planning', 20, 2)->default(0);
            $table->decimal('total_jasa_realisasi', 20, 2)->default(0);
            
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('finance_rekon_boq_details', function (Blueprint $table) {
            $table->id();
            $table->string('finance_rekon_id')->index();
            $table->string('designator');
            $table->text('description')->nullable();
            $table->integer('volume_planning')->default(0);
            $table->decimal('price_planning', 20, 2)->default(0);
            $table->integer('volume_realisasi')->default(0);
            $table->decimal('price_realisasi', 20, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('finance_rekon_id')->references('id')->on('finance_rekons')->onDelete('cascade');
        });

        Schema::table('commerce_rekons', function (Blueprint $table) {
            $table->string('finance_rekon_id')->nullable()->after('id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commerce_rekons', function (Blueprint $table) {
            $table->dropColumn('finance_rekon_id');
        });
        Schema::dropIfExists('finance_rekon_boq_details');
        Schema::dropIfExists('finance_rekons');
    }
};
