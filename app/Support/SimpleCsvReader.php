<?php

namespace App\Support;

class SimpleCsvReader
{
    /**
     * @return array<int, array{name: string, rows: array<int, array<int, string>>}>
     */
    public function read(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Convert 0-indexed array to 1-indexed to match SimpleXlsxReader behavior
                $indexedRow = [];
                foreach ($data as $index => $value) {
                    $indexedRow[$index + 1] = $value;
                }
                $rows[] = $indexedRow;
            }
            fclose($handle);
        }

        return [
            [
                'name' => 'OSP-FO', // Default sheet name for CSV
                'rows' => $rows,
            ],
        ];
    }
}
