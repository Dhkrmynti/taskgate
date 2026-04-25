<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KhsDummySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Batch
        $batchId = DB::table('khs_import_batches')->insertGetId([
            'original_file_name' => 'dummy_khs_refined.xlsx',
            'total_rows' => 50,
            'imported_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create Schema
        $rawHeaders = ['No', 'Designator', 'Designator Material', 'Designator Jasa', 'Uraian Pekerjaan', 'Satuan', 'Paket 5 Material', 'Paket 5 Jasa'];
        $headers = [];
        foreach ($rawHeaders as $h) {
            $key = strtolower(str_replace(' ', '_', $h));
            if ($key === 'no') $key = 'no'; // controller handles this
            $headers[] = ['key' => $key, 'label' => $h];
        }

        DB::table('khs_tab_schemas')->insert([
            'batch_id' => $batchId,
            'tab_key' => 'osp_fo',
            'tab_label' => 'OSP-FO',
            'row_count' => 50,
            'headers' => json_encode($headers),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create Records
        $items = [
            ['Galian Tanah', 'M', 0, 45000],
            ['Pasang Kabel FO 24T', 'M', 12000, 8500],
            ['Pasang Pole 7M', 'BTG', 850000, 150000],
            ['Splicing 1 Core', 'POINT', 0, 25000],
            ['OTDR Test', 'LINK', 0, 150000],
            ['Pasang Closure 24', 'EA', 450000, 75000],
            ['Labeling', 'EA', 5000, 2000],
            ['Transport Material', 'LOT', 0, 500000],
            ['Pembersihan Lokasi', 'M2', 0, 15000],
            ['Dokumentasi & Asbuilt', 'SIT', 0, 1000000],
        ];

        $records = [];
        for ($i = 1; $i <= 50; $i++) {
            $base = $items[($i - 1) % count($items)];
            $num = str_pad($i, 3, '0', STR_PAD_LEFT);
            
            $data = [
                'no' => $i,
                'designator' => 'OSP.' . str_pad(ceil($i/5), 2, '0', STR_PAD_LEFT) . '.' . str_pad(($i-1)%5 + 1, 2, '0', STR_PAD_LEFT),
                'designator_material' => 'M-' . $num,
                'designator_jasa' => 'J-' . $num,
                'uraian_pekerjaan' => $base[0] . ' Type ' . chr(65 + ($i % 3)),
                'satuan' => $base[1],
                'paket_5_material' => $base[2] > 0 ? $base[2] + ($i * 100) : 0,
                'paket_5_jasa' => $base[3] + ($i * 50),
            ];

            $records[] = [
                'batch_id' => $batchId,
                'tab_key' => 'osp_fo',
                'row_number' => $i + 1, // Start after header
                'data' => json_encode($data),
                'search_text' => implode(' ', array_values($data)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($records, 10) as $chunk) {
            DB::table('khs_records')->insert($chunk);
        }

        $this->command->info('50 dummy KHS records created with M- and J- prefix.');
    }
}
