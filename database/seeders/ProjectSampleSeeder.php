<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSampleSeeder extends Seeder
{
    public function run(): void
    {
        $projectId = 'TGIDOP-20260315-1';

        DB::table('projects')->insert([
            'id' => $projectId,
            'project_name' => '26KT211-0001 JPP_THP#3_TSEL_2026',
            'pid_proactive' => $projectId,
            'wbs_sap' => null,
            'customer' => 'PT. Telkomsel',
            'fase' => 'start',
            'portofolio' => 'Enterprise',
            'program' => 'Proactive',
            'jenis_eksekusi' => 'Mitra',
            'branch' => 'Jakarta',
            'pm_project' => 'Raka Pratama',
            'waspang' => 'Bima Santoso',
            'evidence_dasar_path' => 'project-evidences/26kt211/evidence-dasar.rar',
            'boq_file_path' => 'project-evidences/26kt211/boq.xlsx',
            'start_project' => '2026-03-15',
            'end_project' => '2026-04-15',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('project_boq_details')->insert([
            [
                'project_id' => $projectId,
                'designator' => 'M-DC-OF-SM-120',
                'description' => 'Pengadaan dan pemasangan kabel duct fiber optik single',
                'volume_planning' => 1,
                'price_planning' => 100000,
                'volume_pemenuhan' => 0,
                'price_pemenuhan' => 0,
                'volume_aktual' => 0,
                'price_aktual' => 0,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => $projectId,
                'designator' => 'J-DC-OF-SM-120',
                'description' => 'Pengadaan dan pemasangan kabel duct fiber optik single',
                'volume_planning' => 1,
                'price_planning' => 100000,
                'volume_pemenuhan' => 0,
                'price_pemenuhan' => 0,
                'volume_aktual' => 0,
                'price_aktual' => 0,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => $projectId,
                'designator' => 'M-DC-OF-SM-240',
                'description' => 'Pengadaan dan pemasangan kabel duct fiber optik single',
                'volume_planning' => 1,
                'price_planning' => 100000,
                'volume_pemenuhan' => 0,
                'price_pemenuhan' => 0,
                'volume_aktual' => 0,
                'price_aktual' => 0,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => $projectId,
                'designator' => 'J-DC-OF-SM-240',
                'description' => 'Pengadaan dan pemasangan kabel duct fiber optik single',
                'volume_planning' => 1,
                'price_planning' => 100000,
                'volume_pemenuhan' => 0,
                'price_pemenuhan' => 0,
                'volume_aktual' => 0,
                'price_aktual' => 0,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('project_evidences')->insert([
            [
                'project_id' => $projectId,
                'type' => 'boq',
                'label' => 'BoQ',
                'file_name' => 'boq-project.xlsx',
                'file_path' => 'project-evidences/26kt211/boq-project.xlsx',
                'file_extension' => 'xlsx',
                'file_size' => 184320,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('project_evidence_files')->insert([
            [
                'project_id' => $projectId,
                'type' => 'dasar_pekerjaan',
                'label' => 'MoM',
                'file_name' => 'mom-kickoff-project.pdf',
                'file_path' => 'project-evidences/26kt211/dasar/mom-kickoff-project.pdf',
                'file_extension' => 'pdf',
                'file_size' => 320512,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => $projectId,
                'type' => 'dasar_pekerjaan',
                'label' => 'NDE',
                'file_name' => 'nde-approval-project.docx',
                'file_path' => 'project-evidences/26kt211/dasar/nde-approval-project.docx',
                'file_extension' => 'docx',
                'file_size' => 185640,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => $projectId,
                'type' => 'dasar_pekerjaan',
                'label' => 'ASP',
                'file_name' => 'kml-area-project.xlsx',
                'file_path' => 'project-evidences/26kt211/dasar/kml-area-project.xlsx',
                'file_extension' => 'xlsx',
                'file_size' => 146220,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
