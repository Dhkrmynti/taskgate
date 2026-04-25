<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use ZipArchive;

class SimpleXlsxReader
{
    /**
     * @return array<int, array{name: string, rows: array<int, array<int, string>>}>
     */
    public function read(string $path): array
    {
        Log::debug('SimpleXlsxReader open file', [
            'path' => $path,
            'exists' => file_exists($path),
            'readable' => is_readable($path),
        ]);

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('File Excel tidak dapat dibuka.');
        }

        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $workbookRelsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $workbookRelsXml === false) {
            $zip->close();
            throw new RuntimeException('Struktur file Excel tidak valid.');
        }

        $sharedStrings = $this->parseSharedStrings($zip->getFromName('xl/sharedStrings.xml'));
        $sheetTargetsByRelId = $this->parseWorkbookRelations($workbookRelsXml);
        $sheets = $this->parseWorkbookSheets($workbookXml);
        Log::debug('SimpleXlsxReader workbook metadata', [
            'shared_strings_count' => count($sharedStrings),
            'sheet_targets_count' => count($sheetTargetsByRelId),
            'sheets_count' => count($sheets),
        ]);

        $result = [];

        foreach ($sheets as $sheet) {
            $target = $sheetTargetsByRelId[$sheet['rel_id']] ?? null;
            if ($target === null) {
                Log::warning('SimpleXlsxReader missing relationship target for sheet', [
                    'sheet' => $sheet['name'] ?? null,
                    'rel_id' => $sheet['rel_id'] ?? null,
                ]);
                continue;
            }

            $sheetPath = $this->normalizeSheetPath($target);
            $sheetXml = $zip->getFromName($sheetPath);

            if ($sheetXml === false) {
                Log::warning('SimpleXlsxReader sheet xml not found', [
                    'sheet' => $sheet['name'] ?? null,
                    'target' => $target,
                    'normalized_path' => $sheetPath,
                ]);
                continue;
            }

            $result[] = [
                'name' => $sheet['name'],
                'rows' => $this->parseSheetRows($sheetXml, $sharedStrings),
            ];
        }

        $zip->close();

        return $result;
    }

    /**
     * @return array<int, string>
     */
    private function parseSharedStrings(string|false $xmlContent): array
    {
        if ($xmlContent === false) {
            return [];
        }

        $xpath = $this->createXPath($xmlContent);
        if ($xpath === null) {
            return [];
        }

        $nodes = $xpath->query('//*[local-name()="si"]');
        if ($nodes === false) {
            return [];
        }

        $sharedStrings = [];

        foreach ($nodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $textNodes = $xpath->query('.//*[local-name()="t"]', $node);
            $combined = '';

            if ($textNodes !== false) {
                foreach ($textNodes as $textNode) {
                    $combined .= (string) $textNode->textContent;
                }
            }

            $sharedStrings[] = trim($combined);
        }

        return $sharedStrings;
    }

    /**
     * @return array<string, string>
     */
    private function parseWorkbookRelations(string $xmlContent): array
    {
        $xpath = $this->createXPath($xmlContent);
        if ($xpath === null) {
            return [];
        }

        $nodes = $xpath->query('//*[local-name()="Relationship"]');
        if ($nodes === false) {
            return [];
        }

        $targets = [];

        foreach ($nodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $id = trim((string) $node->getAttribute('Id'));
            $target = trim((string) $node->getAttribute('Target'));

            if ($id !== '' && $target !== '') {
                $targets[$id] = $target;
            }
        }

        return $targets;
    }

    /**
     * @return array<int, array{name: string, rel_id: string}>
     */
    private function parseWorkbookSheets(string $xmlContent): array
    {
        $xpath = $this->createXPath($xmlContent);
        if ($xpath === null) {
            return [];
        }

        $nodes = $xpath->query('//*[local-name()="sheets"]/*[local-name()="sheet"]');
        if ($nodes === false) {
            return [];
        }

        $sheets = [];

        foreach ($nodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $name = trim((string) $node->getAttribute('name'));
            $relId = trim((string) $node->getAttributeNS(
                'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
                'id'
            ));

            if ($relId === '') {
                foreach ($node->attributes ?? [] as $attribute) {
                    if ($attribute->localName === 'id') {
                        $relId = trim((string) $attribute->nodeValue);
                        break;
                    }
                }
            }

            if ($name === '' || $relId === '') {
                continue;
            }

            $sheets[] = [
                'name' => $name,
                'rel_id' => $relId,
            ];
        }

        return $sheets;
    }

    private function normalizeSheetPath(string $target): string
    {
        $path = str_replace('\\', '/', trim($target));
        $path = ltrim($path, '/');

        while (str_starts_with($path, '../')) {
            $path = substr($path, 3);
        }

        if (str_starts_with($path, 'xl/')) {
            return $path;
        }

        return 'xl/'.$path;
    }

    /**
     * @param  array<int, string>  $sharedStrings
     * @return array<int, array<int, string>>
     */
    private function parseSheetRows(string $sheetXml, array $sharedStrings): array
    {
        $xpath = $this->createXPath($sheetXml);
        if ($xpath === null) {
            return [];
        }

        $rowNodes = $xpath->query('//*[local-name()="sheetData"]/*[local-name()="row"]');
        if ($rowNodes === false) {
            return [];
        }

        $rows = [];

        foreach ($rowNodes as $rowNode) {
            if (! $rowNode instanceof DOMElement) {
                continue;
            }

            $cellNodes = $xpath->query('./*[local-name()="c"]', $rowNode);
            if ($cellNodes === false) {
                continue;
            }

            $rowData = [];

            foreach ($cellNodes as $cellNode) {
                if (! $cellNode instanceof DOMElement) {
                    continue;
                }

                $reference = trim((string) $cellNode->getAttribute('r'));
                $columnIndex = $this->cellReferenceToColumnIndex($reference);
                $rowData[$columnIndex] = $this->parseCellValue($xpath, $cellNode, $sharedStrings);
            }

            if ($rowData === []) {
                continue;
            }

            ksort($rowData);
            $rows[] = $rowData;
        }

        return $rows;
    }

    /**
     * @param  array<int, string>  $sharedStrings
     */
    private function parseCellValue(DOMXPath $xpath, DOMElement $cellNode, array $sharedStrings): string
    {
        $type = trim((string) $cellNode->getAttribute('t'));

        if ($type === 'inlineStr') {
            $textNodes = $xpath->query('./*[local-name()="is"]/*[local-name()="t"]', $cellNode);
            $value = '';

            if ($textNodes !== false) {
                foreach ($textNodes as $textNode) {
                    $value .= (string) $textNode->textContent;
                }
            }

            return trim($value);
        }

        $valueNodes = $xpath->query('./*[local-name()="v"]', $cellNode);
        $rawValue = '';

        if ($valueNodes !== false && $valueNodes->length > 0) {
            $rawValue = (string) $valueNodes->item(0)?->textContent;
        }

        if ($type === 's') {
            $index = (int) $rawValue;
            return trim($sharedStrings[$index] ?? '');
        }

        return trim($rawValue);
    }

    private function cellReferenceToColumnIndex(string $reference): int
    {
        if ($reference === '') {
            return 1;
        }

        $letters = preg_replace('/[^A-Z]/', '', strtoupper($reference)) ?? 'A';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max($index, 1);
    }

    private function createXPath(string $xmlContent): ?DOMXPath
    {
        $dom = new DOMDocument();
        $loaded = @$dom->loadXML($xmlContent);

        if (! $loaded) {
            Log::warning('SimpleXlsxReader failed to parse XML fragment');
            return null;
        }

        return new DOMXPath($dom);
    }
}
