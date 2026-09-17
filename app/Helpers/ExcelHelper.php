<?php
namespace App\Helpers;

use ZipArchive;
use SimpleXMLElement;
use Exception;

class ExcelHelper
{
    /**
     * Konversi nomor kolom (0-based) ke huruf kolom Excel (0 => A, 25 => Z, 26 => AA, dst)
     */
    public static function colLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex >= 0) {
            $letter = chr($colIndex % 26 + 65) . $letter;
            $colIndex = intval($colIndex / 26) - 1;
        }
        return $letter;
    }

    /**
     * Konversi referensi sel Excel (misal: "AA12", "C5") ke index kolom 0-based
     */
    public static function colIndexFromRef(string $cellRef): int
    {
        $colLetters = strtoupper(preg_replace('/[^a-zA-Z]/', '', $cellRef));
        $len = strlen($colLetters);
        $idx = 0;
        for ($i = 0; $i < $len; $i++) {
            $idx = $idx * 26 + (ord($colLetters[$i]) - 64);
        }
        return $idx - 1;
    }

    /**
     * Buat berkas XLSX native (OpenXML Spreadsheet)
     */
    public static function createXlsx(string $sheetName, array $headers, array $rows): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Gagal membuat arsip XLSX sementara.");
        }

        // [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // xl/workbook.xml
        $cleanSheetName = htmlspecialchars(substr($sheetName, 0, 31), ENT_XML1, 'UTF-8');
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="' . $cleanSheetName . '" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbook);

        // xl/styles.xml (Gaya Header & Cell)
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><name val="Calibri"/><sz val="11"/><color rgb="FF000000"/></font>
    <font><b/><name val="Calibri"/><sz val="11"/><color rgb="FFFFFFFF"/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF0F172A"/></patternFill></fill>
  </fills>
  <borders count="1">
    <border><left/><right/><top/><bottom/></border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="2">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>
  </cellXfs>
</styleSheet>';
        $zip->addFromString('xl/styles.xml', $styles);

        // xl/worksheets/sheet1.xml
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetData>';

        // Baris Header (Style 1: Dark Slate Background, White Bold Text)
        $r = 1;
        $xml .= '<row r="' . $r . '">';
        foreach ($headers as $c => $h) {
            $cellRef = self::colLetter($c) . $r;
            $escaped = htmlspecialchars((string)$h, ENT_XML1, 'UTF-8');
            $xml .= '<c r="' . $cellRef . '" t="inlineStr" s="1"><is><t>' . $escaped . '</t></is></c>';
        }
        $xml .= '</row>';

        // Baris Data (Style 0)
        foreach ($rows as $row) {
            $r++;
            $xml .= '<row r="' . $r . '">';
            $rowValues = array_values($row);
            foreach ($rowValues as $c => $val) {
                $cellRef = self::colLetter($c) . $r;
                $escaped = htmlspecialchars((string)$val, ENT_XML1, 'UTF-8');
                // Simpan selalu sebagai inlineStr agar angka seperti NISN "008123" tidak hilang leading 0
                $xml .= '<c r="' . $cellRef . '" t="inlineStr" s="0"><is><t>' . $escaped . '</t></is></c>';
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData>';
        $xml .= '</worksheet>';

        $zip->addFromString('xl/worksheets/sheet1.xml', $xml);
        $zip->close();

        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content;
    }

    /**
     * Membaca file spreadsheet (.xlsx, .xls, .csv) secara otomatis
     */
    public static function parse(string $filePath, ?string $originalFileName = null): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("Berkas tidak dapat dibaca atau tidak ditemukan.");
        }

        $fileName = strtolower($originalFileName ?: basename($filePath));

        // 1. Coba baca sebagai XLSX (ZIP archive)
        if (str_ends_with($fileName, '.xlsx') || self::isZipFile($filePath)) {
            try {
                return self::parseXlsx($filePath);
            } catch (\Throwable $e) {
                // Lanjut ke fallback jika gagal
            }
        }

        // 2. Cek jika XLS berupa HTML table (sering diekspor oleh sistem lama)
        $firstBytes = file_get_contents($filePath, false, null, 0, 500);
        if (stripos($firstBytes, '<table') !== false || stripos($firstBytes, '<html') !== false) {
            return self::parseHtmlTable($filePath);
        }

        // 3. Cek jika XML Spreadsheet 2003
        if (stripos($firstBytes, '<?xml') !== false && stripos($firstBytes, 'Workbook') !== false) {
            return self::parseXmlSpreadsheet($filePath);
        }

        // 4. Default fallback: Parse sebagai CSV / Delimited
        return self::parseDelimited($filePath);
    }

    private static function isZipFile(string $filePath): bool
    {
        $h = @fopen($filePath, 'r');
        if (!$h) return false;
        $sig = fread($h, 4);
        fclose($h);
        return ($sig === "PK\x03\x04");
    }

    /**
     * Parse file XLSX murni OpenXML
     */
    public static function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception("Berkas Excel (.xlsx) tidak valid atau rusak.");
        }

        // Ambil Shared Strings jika ada
        $sharedStrings = [];
        $ssContent = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssContent !== false) {
            $xml = simplexml_load_string($ssContent);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string)$r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // Cari sheet1.xml atau sheet pertama
        $sheetContent = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetContent === false) {
            // Coba cari berkas sheet pertama apapun namanya
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (preg_match('#^xl/worksheets/sheet\d+\.xml$#', $stat['name'])) {
                    $sheetContent = $zip->getFromName($stat['name']);
                    break;
                }
            }
        }
        $zip->close();

        if ($sheetContent === false) {
            throw new Exception("Lembar kerja (worksheet) tidak ditemukan di dalam berkas Excel.");
        }

        $xml = simplexml_load_string($sheetContent);
        if (!$xml || !isset($xml->sheetData)) {
            throw new Exception("Format lembar kerja Excel tidak dapat diproses.");
        }

        $rows = [];
        foreach ($xml->sheetData->row as $rowNode) {
            $rowCells = [];
            $maxCol = 0;

            foreach ($rowNode->c as $cell) {
                $cellRef = (string)$cell['r'];
                $colIdx = self::colIndexFromRef($cellRef);
                if ($colIdx < 0) continue;

                $type = (string)$cell['t'];
                $val = '';

                if ($type === 'inlineStr') {
                    $val = (string)$cell->is->t;
                } elseif ($type === 's') {
                    $idx = (int)$cell->v;
                    $val = $sharedStrings[$idx] ?? '';
                } elseif ($type === 'b') {
                    $val = ((string)$cell->v === '1') ? '1' : '0';
                } else {
                    $val = (string)$cell->v;
                }

                $rowCells[$colIdx] = trim($val);
                if ($colIdx > $maxCol) $maxCol = $colIdx;
            }

            // Normalisasi baris dengan array terindeks berurutan
            if (!empty($rowCells)) {
                $normalized = [];
                for ($c = 0; $c <= $maxCol; $c++) {
                    $normalized[$c] = $rowCells[$c] ?? '';
                }
                $rows[] = $normalized;
            }
        }

        return $rows;
    }

    /**
     * Parse HTML Table yang disimpan sebagai .XLS
     */
    private static function parseHtmlTable(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $content);
        $tables = $dom->getElementsByTagName('table');
        if ($tables->length === 0) return [];

        $table = $tables->item(0);
        $rows = [];
        foreach ($table->getElementsByTagName('tr') as $tr) {
            $cells = [];
            foreach ($tr->getElementsByTagName('th') as $th) {
                $cells[] = trim($th->textContent);
            }
            if (empty($cells)) {
                foreach ($tr->getElementsByTagName('td') as $td) {
                    $cells[] = trim($td->textContent);
                }
            }
            if (!empty($cells)) {
                $rows[] = $cells;
            }
        }
        return $rows;
    }

    /**
     * Parse XML Spreadsheet 2003
     */
    private static function parseXmlSpreadsheet(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $content = preg_replace('/(<\/?)(ss:)/', '$1', $content);
        $xml = simplexml_load_string($content);
        if (!$xml) return [];

        $rows = [];
        if (isset($xml->Worksheet->Table->Row)) {
            foreach ($xml->Worksheet->Table->Row as $rowNode) {
                $cells = [];
                foreach ($rowNode->Cell as $cell) {
                    $cells[] = trim((string)$cell->Data);
                }
                if (!empty($cells)) {
                    $rows[] = $cells;
                }
            }
        }
        return $rows;
    }

    /**
     * Parse CSV / Text Delimited
     */
    private static function parseDelimited(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // Hapus BOM
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $content));
        $lines = array_filter(array_map('trim', $lines));
        if (empty($lines)) return [];

        // Deteksi delimiter
        $firstLine = $lines[0];
        $delimiters = [';', ',', "\t"];
        $detected = ',';
        $maxCount = 0;
        foreach ($delimiters as $d) {
            $cnt = substr_count($firstLine, $d);
            if ($cnt > $maxCount) {
                $maxCount = $cnt;
                $detected = $d;
            }
        }

        $rows = [];
        foreach ($lines as $line) {
            if ($line === '') continue;
            $row = str_getcsv($line, $detected);
            $rows[] = array_map('trim', $row);
        }
        return $rows;
    }
}
