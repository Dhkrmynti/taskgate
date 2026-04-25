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
        // 1. Create Warehouse Rekons Table
        if (!Schema::hasTable('warehouse_rekons')) {
            Schema::create('warehouse_rekons', function (Blueprint $table) {
                $table->string('id')->primary(); // TGIDRM format
                $table->string('rekon_number')->nullable();
                $table->string('rekon_file_path')->nullable();
                $table->string('boq_file_path')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 2. Add warehouse_rekon_id to Project Batches Table (TGIDSP)
        Schema::table('project_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('project_batches', 'warehouse_rekon_id')) {
                $table->string('warehouse_rekon_id')->nullable()->after('rekon_id');
                $table->foreign('warehouse_rekon_id')->references('id')->on('warehouse_rekons')->nullOnDelete();
            }
        });

        // 3. Create Warehouse Rekon BoQ Details Table
        if (!Schema::hasTable('warehouse_rekon_boq_details')) {
            Schema::create('warehouse_rekon_boq_details', function (Blueprint $table) {
                $table->id();
                $table->string('warehouse_rekon_id');
                $table->foreign('warehouse_rekon_id')->references('id')->on('warehouse_rekons')->cascadeOnDelete();
                $table->string('designator');
                $table->text('description');
                $table->unsignedInteger('volume_planning')->default(0);
                $table->decimal('price_planning', 15, 2)->default(0);
                $table->unsignedInteger('volume_pemenuhan')->default(0);
                $table->unsignedInteger('volume_aktual')->default(0);
                $table->decimal('price_aktual', 15, 2)->default(0);
                $table->integer('volume_deviasi')->default(0); // New column
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
        Schema::dropIfExists('warehouse_rekon_boq_details');
        
        Schema::table('project_batches', function (Blueprint $table) {
            if (Schema::hasColumn('project_batches', 'warehouse_rekon_id')) {
                $table->dropForeign(['warehouse_rekon_id']);
                $table->dropColumn('warehouse_rekon_id');
            }
        });

        Schema::dropIfExists('warehouse_rekons');
    }
};
