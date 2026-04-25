<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        $childTables = [
            'project_boq_details' => ['indices' => [['name' => 'project_boq_details_project_id_sort_order_index', 'cols' => ['project_id', 'sort_order']]]],
            'project_evidences' => ['indices' => [['name' => 'project_evidences_project_id_type_index', 'cols' => ['project_id', 'type']]]],
            'project_evidence_files' => ['indices' => [['name' => 'project_evidence_files_project_id_type_sort_order_index', 'cols' => ['project_id', 'type', 'sort_order']]]],
            'project_subfase_statuses' => ['indices' => [['name' => 'project_subfase_statuses_project_id_subfase_key_unique', 'cols' => ['project_id', 'subfase_key'], 'unique' => true]]]
        ];

        // 1. Drop Foreign Keys and convert child columns to VARCHAR to avoid type mismatch on update
        foreach (array_keys($childTables) as $table) {
            try { Schema::table($table, fn($t) => $t->dropForeign(['project_id'])); } catch (\Exception $e) {}
            try { DB::statement("ALTER TABLE $table MODIFY project_id VARCHAR(32) NOT NULL"); } catch (\Exception $e) {}
        }

        // 2. Convert Projects ID to VARCHAR
        $idType = Schema::getColumnType('projects', 'id');
        if (!str_contains(strtolower($idType), 'varchar') && !str_contains(strtolower($idType), 'string')) {
            try { DB::statement('ALTER TABLE projects MODIFY id BIGINT UNSIGNED NOT NULL'); } catch (\Exception $e) {}
            try { DB::statement('ALTER TABLE projects DROP PRIMARY KEY'); } catch (\Exception $e) {}
            try { DB::statement('ALTER TABLE projects MODIFY id VARCHAR(32) NOT NULL'); } catch (\Exception $e) {}
            try { DB::statement('ALTER TABLE projects ADD PRIMARY KEY (id)'); } catch (\Exception $e) {}
        }

        // 3. Migrate Data Mapping
        $projects = DB::table('projects')->orderBy('created_at')->orderBy('id')->get();
        foreach ($projects as $index => $project) {
            $legacyIdExpected = (string)($index + 1);
            $currentId = $project->id;
            $newId = $currentId;

            if (is_numeric($currentId)) {
                $datePart = \Illuminate\Support\Carbon::parse($project->created_at)->format('Ymd');
                $newId = sprintf('TGID-%s-%04d', $datePart, $index + 1);
                DB::table('projects')->where('id', $currentId)->update(['id' => $newId]);
            }

            // Update children mapping (legacy numerical string to new TGID)
            foreach (array_keys($childTables) as $table) {
                DB::table($table)->where('project_id', $legacyIdExpected)->update(['project_id' => $newId]);
            }

            // Update activity logs
            DB::table('activity_logs')
                ->where('target_type', 'Project')
                ->where('target_id', $legacyIdExpected)
                ->update(['target_id' => $newId]);
        }

        // 4. Transform activity_logs.target_id to VARCHAR
        try { DB::statement('ALTER TABLE activity_logs MODIFY target_id VARCHAR(32) NULL'); } catch (\Exception $e) {}

        // 5. Re-create Indices and Foreign Keys
        foreach ($childTables as $table => $meta) {
            // Drop old indices just in case
            foreach ($meta['indices'] as $idx) {
                try { Schema::table($table, fn($t) => $t->dropIndex($idx['name'])); } catch (\Exception $e) {}
            }

            // Re-create indices
            Schema::table($table, function (Blueprint $t) use ($meta) {
                foreach ($meta['indices'] as $indexMeta) {
                    if (isset($indexMeta['unique']) && $indexMeta['unique']) {
                        $t->unique($indexMeta['cols'], $indexMeta['name']);
                    } else {
                        $t->index($indexMeta['cols'], $indexMeta['name']);
                    }
                }
            });

            // Re-add Foreign Key
            Schema::table($table, function (Blueprint $t) {
                $t->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            });
        }

        // 6. Cleanup any temporary columns if they were created in previous attempts
        foreach (['projects' => 'new_id', 'project_boq_details' => 'project_id_new', 'project_evidences' => 'project_id_new', 'project_evidence_files' => 'project_id_new', 'project_subfase_statuses' => 'project_id_new', 'activity_logs' => 'target_id_new'] as $t => $c) {
            if (Schema::hasColumn($t, $c)) {
                Schema::table($t, fn($table) => $table->dropColumn($c));
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // One-way migration
    }
};
