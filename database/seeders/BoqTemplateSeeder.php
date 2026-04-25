<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BoqTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $outputPath = storage_path('app/templates');

        if (! is_dir($outputPath)) {
            mkdir($outputPath, 0755, true);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('BOQ PLANNING');

        $headers = [
            'A1' => 'No',
            'B1' => 'Designator',
            'C1' => 'Volume',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E79'],
            ],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

        $sheet->setCellValue('A2', 1);
        $sheet->setCellValue('B2', 'M-001');
        $sheet->setCellValue('C2', 10);

        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath . '/boq-planning-template.xlsx');
        
        $this->command->info('Template BoQ Planning berhasil dibuat di: ' . $outputPath . '/boq-planning-template.xlsx');
    }
}
