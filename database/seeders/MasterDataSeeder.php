<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'customers' => ['PT. Telkomsel', 'PT. Telkom Indonesia', 'PT. PLN'],
            'portofolios' => ['Enterprise', 'Consumer', 'Government'],
            'programs' => ['Proactive', 'Migration', 'Modernization'],
            'execution_types' => ['Internal', 'Mitra', 'Hybrid'],
            'branches' => ['Jakarta', 'Bandung', 'Surabaya', 'Makassar'],
            'pm_projects' => ['Raka Pratama', 'Dian Kurnia', 'Andi Saputra'],
            'waspangs' => ['Bima Santoso', 'Rizki Maulana', 'Dewi Lestari'],
        ];

        foreach ($data as $table => $rows) {
            foreach ($rows as $name) {
                DB::table($table)->updateOrInsert(
                    ['name' => $name],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            }
        }

    }
}
