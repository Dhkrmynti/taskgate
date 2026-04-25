<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use ZipArchive;
use XMLReader;

class StreamXlsxReader
{
    /**
     * Read Excel sheets using a streaming approach to save memory.
     * Returns a Generator to yield rows one by one.
     */
    public function readStream(string $path): \Generator
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('File Excel tidak dapat dibuka.');
        }

        // 1. Get Shared Strings (still need to load this into memory, but usually it's manageable)
        $sharedStrings = $this->getSharedStrings($zip);
        
        // 2. Get Sheets
        $sheets = $this->getSheets($zip);

        foreach ($sheets as $sheet) {
            $sheetPath = $sheet['path'];
            $stream = $zip->getStream($sheetPath);
            if (!$stream) continue;

            $reader = new XMLReader();
            $reader->XML(stream_get_contents($stream));
            fclose($stream);

            $currentRow = [];
            
            yield [
                'name' => $sheet['name'],
                'rows' => $this->iterateRows($reader, $sharedStrings)
            ];

            $reader->close();
        }

        $zip->close();
    }

    private function iterateRows(XMLReader $reader, array $sharedStrings): \Generator
    {
        while ($reader->read()) {
            if ($reader->nodeType == XMLReader::ELEMENT && $reader->localName == 'row') {
                $rowData = [];
                $rowReader = $reader->readInnerXml();
                $cellReader = new XMLReader();
                $cellReader->XML("<row>$rowReader</row>");
                
                while ($cellReader->read()) {
                    if ($cellReader->nodeType == XMLReader::ELEMENT && $cellReader->localName == 'c') {
                        $ref = $cellReader->getAttribute('r');
                        $type = $cellReader->getAttribute('t');
                        $colIdx = $this->cellReferenceToColumnIndex($ref);
                        
                        // Move to value node
                        $val = '';
                        while ($cellReader->read()) {
                            if ($cellReader->nodeType == XMLReader::ELEMENT && $cellReader->localName == 'v') {
                                $val = $cellReader->readString();
                                break;
                            }
                            if ($cellReader->nodeType == XMLReader::END_ELEMENT && $cellReader->localName == 'c') {
                                break;
                            }
                        }
                        
                        if ($type === 's') {
                            $val = $sharedStrings[(int)$val] ?? '';
                        }
                        
                        $rowData[$colIdx] = trim($val);
                    }
                }
                $cellReader->close();
                
                if (!empty($rowData)) {
                    ksort($rowData);
                    yield $rowData;
                }
            }
        }
    }

    private function getSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if (!$xml) return [];

        $reader = new XMLReader();
        $reader->XML($xml);
        $strings = [];
        
        while ($reader->read()) {
            if ($reader->nodeType == XMLReader::ELEMENT && $reader->localName == 't') {
                $strings[] = trim($reader->readString());
            }
        }
        $reader->close();
        return $strings;
    }

    private function getSheets(ZipArchive $zip): array
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if (!$workbookXml || !$relsXml) return [];

        // Simple manual parsing of rels to avoid DOM
        $targets = [];
        $reader = new XMLReader();
        $reader->XML($relsXml);
        while ($reader->read()) {
            if ($reader->nodeType == XMLReader::ELEMENT && $reader->localName == 'Relationship') {
                $targets[$reader->getAttribute('Id')] = $reader->getAttribute('Target');
            }
        }
        $reader->close();

        $sheets = [];
        $reader->XML($workbookXml);
        while ($reader->read()) {
            if ($reader->nodeType == XMLReader::ELEMENT && $reader->localName == 'sheet') {
                $relId = $reader->getAttribute('r:id') ?: $reader->getAttribute('id');
                $target = $targets[$relId] ?? '';
                $path = str_contains($target, 'xl/') ? $target : 'xl/' . $target;
                
                $sheets[] = [
                    'name' => $reader->getAttribute('name'),
                    'path' => $path
                ];
            }
        }
        $reader->close();
        return $sheets;
    }

    private function cellReferenceToColumnIndex(string $reference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($reference)) ?: 'A';
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }
        return max($index, 1);
    }
}
