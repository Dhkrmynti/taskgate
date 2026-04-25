<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class KhsTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $outputPath = storage_path('app/templates');

        if (! is_dir($outputPath)) {
            mkdir($outputPath, 0755, true);
        }

        $this->generateOspFoTemplate($outputPath);

        $this->command->info('Template KHS OSP-FO berhasil dibuat di: ' . $outputPath . '/khs-ospfo-template.xlsx');
    }

    private function generateOspFoTemplate(string $outputPath): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('OSP-FO');

        // --- Header Baris 1 (merged) ---
        $headers = [
            'A1' => 'No',
            'B1' => 'Designator',
            'C1' => 'Designator Material',
            'D1' => 'Designator Jasa',
            'E1' => 'Uraian Pekerjaan',
            'F1' => 'Satuan',
            'G1' => 'Paket 5 Material',
            'H1' => 'Paket 5 Jasa',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style header
        $headerStyle = [
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E79'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // --- Contoh data baris 2 ---
        $sheet->setCellValue('A2', 1);
        $sheet->setCellValue('B2', 'OSP.01.01');
        $sheet->setCellValue('C2', 'DM-01');
        $sheet->setCellValue('D2', 'DJ-01');
        $sheet->setCellValue('E2', 'Pekerjaan Galian Tanah');
        $sheet->setCellValue('F2', 'M');
        $sheet->setCellValue('G2', 0);
        $sheet->setCellValue('H2', 45500);

        // Style baris data contoh
        $dataStyle = [
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A2:H2')->applyFromArray($dataStyle);

        // Border seluruh tabel
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['rgb' => 'AAAAAA'],
                ],
            ],
        ];
        $sheet->getStyle('A1:H2')->applyFromArray($borderStyle);

        // Lebar kolom
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(35);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(18);

        // Freeze header row
        $sheet->freezePane('A2');

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath . '/khs-ospfo-template.xlsx');
    }
}
