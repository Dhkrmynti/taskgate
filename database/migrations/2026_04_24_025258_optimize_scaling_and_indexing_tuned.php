<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Full-Text Search for Finance & Warehouse (RAW SQL for better driver compatibility)
        try {
            DB::statement('ALTER TABLE finance_records ADD FULLTEXT search_text_fulltext (search_text)');
        } catch (\Exception $e) {}

        try {
            DB::statement('ALTER TABLE warehouse_records ADD FULLTEXT search_text_fulltext (search_text)');
        } catch (\Exception $e) {}

        // 2. Missing Indexes for Project Management & Scaling
        Schema::table('project_batches', function (Blueprint $table) {
            if (!IndexDetails::hasIndex('project_batches', 'project_batches_mitra_id_index')) {
                $table->index('mitra_id');
            }
            if (!IndexDetails::hasIndex('project_batches', 'project_batches_commerce_rekon_id_index')) {
                $table->index('commerce_rekon_id');
            }
            if (!IndexDetails::hasIndex('project_batches', 'project_batches_warehouse_rekon_id_index')) {
                $table->index('warehouse_rekon_id');
            }
            if (!IndexDetails::hasIndex('project_batches', 'project_batches_finance_rekon_id_index')) {
                $table->index('finance_rekon_id');
            }
        });

        Schema::table('project_boq_details', function (Blueprint $table) {
            if (!IndexDetails::hasIndex('project_boq_details', 'boq_project_designator_idx')) {
                $table->index(['project_id', 'designator'], 'boq_project_designator_idx');
            }
        });

        // 3. Activity Log Indexing
        Schema::table('activity_logs', function (Blueprint $table) {
            if (!IndexDetails::hasIndex('activity_logs', 'activity_logs_module_index')) {
                $table->index('module');
            }
            if (!IndexDetails::hasIndex('activity_logs', 'activity_logs_target_id_target_type_index')) {
                $table->index(['target_id', 'target_type']);
            }
        });
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE finance_records DROP INDEX search_text_fulltext');
        } catch (\Exception $e) {}

        try {
            DB::statement('ALTER TABLE warehouse_records DROP INDEX search_text_fulltext');
        } catch (\Exception $e) {}

        Schema::table('project_batches', function (Blueprint $table) {
            $table->dropIndex(['mitra_id']);
            $table->dropIndex(['commerce_rekon_id']);
            $table->dropIndex(['warehouse_rekon_id']);
            $table->dropIndex(['finance_rekon_id']);
        });

        Schema::table('project_boq_details', function (Blueprint $table) {
            $table->dropIndex('boq_project_designator_idx');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['module']);
            $table->dropIndex(['target_id', 'target_type']);
        });
    }
};

/**
 * Helper class to check for existing indexes
 */
class IndexDetails {
    public static function hasIndex($table, $indexName) {
        $conn = Schema::getConnection();
        $dbName = $conn->getDatabaseName();
        $results = DB::select("SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?", [$dbName, $table, $indexName]);
        return count($results) > 0;
    }
}
