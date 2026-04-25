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
        // 1. Create Commerce Rekons Table
        if (!Schema::hasTable('commerce_rekons')) {
            Schema::create('commerce_rekons', function (Blueprint $table) {
                $table->string('id')->primary(); // TGIDRC format
                $table->string('rekon_number')->nullable();
                $table->string('rekon_file_path')->nullable();
                $table->string('boq_file_path')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 2. Add rekon_id to Project Batches Table (TGIDSP)
        Schema::table('project_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('project_batches', 'rekon_id')) {
                $table->string('rekon_id')->nullable()->after('id');
                $table->foreign('rekon_id')->references('id')->on('commerce_rekons')->nullOnDelete();
            }
        });

        // 3. Create Commerce Rekon BoQ Details Table
        if (!Schema::hasTable('commerce_rekon_boq_details')) {
            Schema::create('commerce_rekon_boq_details', function (Blueprint $table) {
                $table->id();
                $table->string('commerce_rekon_id');
                $table->foreign('commerce_rekon_id')->references('id')->on('commerce_rekons')->cascadeOnDelete();
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
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commerce_rekon_boq_details');
        
        Schema::table('project_batches', function (Blueprint $table) {
            $table->dropForeign(['rekon_id']);
            $table->dropColumn('rekon_id');
        });

        Schema::dropIfExists('commerce_rekons');
    }
};
