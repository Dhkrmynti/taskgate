<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectBoqDetail;
use App\Models\ProjectEvidenceFile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FreshSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Cleanup all TGIDSP (Batches) references
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('project_batches')->truncate();
        DB::table('project_batch_boq_details')->truncate();
        DB::table('project_batch_subfase_statuses')->truncate();
        DB::table('procurement_sps')->truncate();
        
        // Reset existing projects to standalone state
        Project::whereNotNull('batch_id')->update([
            'batch_id' => null,
            'fase' => 'start'
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Create 5 Fresh Dummy Sites (TGIDOP)
        $branches = ['Jakarta', 'Bandung', 'Surabaya', 'Makassar', 'Medan'];
        $customers = ['Telkomsel', 'Indosat', 'XL Axiata', 'Smartfren'];

        for ($i = 1; $i <= 5; $i++) {
            $id = Project::generateNextIdentifier();
            $project = Project::create([
                'id' => $id,
                'project_name' => "Dummy Site Proyek " . chr(64 + $i),
                'customer' => $customers[array_rand($customers)],
                'branch' => $branches[array_rand($branches)],
                'fase' => 'start',
                'start_project' => now(),
                'end_project' => now()->addDays(30),
            ]);

            // Add BoQ Items (Required for Batching)
            for ($j = 1; $j <= 3; $j++) {
                ProjectBoqDetail::create([
                    'project_id' => $id,
                    'designator' => "DW-0{$j}",
                    'description' => "Item Pekerjaan Dummy {$j} untuk " . $id,
                    'volume_planning' => rand(10, 50),
                    'price_planning' => rand(100000, 500000),
                    'sort_order' => $j,
                ]);
            }

            // Add Dasar Pekerjaan Evidence (Required for Batching)
            ProjectEvidenceFile::create([
                'project_id' => $id,
                'type' => 'dasar_pekerjaan',
                'file_name' => "dummy_dp_{$i}.pdf",
                'file_path' => "evidence/dummy/dummy_dp_{$i}.pdf",
                'file_extension' => 'pdf',
                'file_size' => 1024 * 1024,
                'sort_order' => 1,
            ]);
        }
    }
}
