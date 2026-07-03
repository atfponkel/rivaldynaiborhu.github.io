<?php
/**
 * SimpleXLSXWriter
 * Library minimal untuk membuat file .xlsx murni dengan PHP native (ZipArchive),
 * tanpa dependency eksternal / Composer. Cukup untuk export tabel data sederhana.
 */
class SimpleXLSXWriter
{
    private array $rows = [];
    private array $headers = [];

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function addRow(array $row): void
    {
        $this->rows[] = $row;
    }

    public function setRows(array $rows): void
    {
        $this->rows = $rows;
    }

    private function colName(int $index): string
    {
        // index 0-based -> A, B, ..., Z, AA, AB, ...
        $name = '';
        $index++;
        while ($index > 0) {
            $rem = ($index - 1) % 26;
            $name = chr(65 + $rem) . $name;
            $index = intdiv($index - 1, 26);
        }
        return $name;
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function cellXml(int $colIdx, int $rowIdx, $value): string
    {
        $ref = $this->colName($colIdx) . $rowIdx;

        if ($value === null || $value === '') {
            return "<c r=\"$ref\"/>";
        }

        if (is_numeric($value) && !preg_match('/^0[0-9]/', (string)$value)) {
            // angka murni (hindari menghilangkan leading zero, misal kode cabang "0123")
            $num = (string)$value;
            return "<c r=\"$ref\"><v>" . $this->escapeXml($num) . "</v></c>";
        }

        $text = $this->escapeXml((string)$value);
        return "<c r=\"$ref\" t=\"inlineStr\"><is><t xml:space=\"preserve\">$text</t></is></c>";
    }

    private function buildSheetXml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

        // Freeze header row + autofilter
        $colCount = count($this->headers);
        $rowCount = count($this->rows) + 1;
        $lastCol = $this->colName(max(0, $colCount - 1));
        $xml .= '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';

        $xml .= '<cols>';
        for ($i = 0; $i < $colCount; $i++) {
            $xml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="20" customWidth="1"/>';
        }
        $xml .= '</cols>';

        $xml .= '<sheetData>';

        // Header row (style 1 = bold header)
        $xml .= '<row r="1">';
        foreach ($this->headers as $idx => $h) {
            $ref = $this->colName($idx) . '1';
            $text = $this->escapeXml((string)$h);
            $xml .= "<c r=\"$ref\" t=\"inlineStr\" s=\"1\"><is><t xml:space=\"preserve\">$text</t></is></c>";
        }
        $xml .= '</row>';

        // Data rows
        $r = 2;
        foreach ($this->rows as $row) {
            $xml .= '<row r="' . $r . '">';
            foreach ($this->headers as $idx => $h) {
                $val = $row[$h] ?? '';
                $xml .= $this->cellXml($idx, $r, $val);
            }
            $xml .= '</row>';
            $r++;
        }

        $xml .= '</sheetData>';

        if ($colCount > 0 && $rowCount > 1) {
            $xml .= '<autoFilter ref="A1:' . $lastCol . $rowCount . '"/>';
        }

        $xml .= '</worksheet>';
        return $xml;
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbookXml(string $sheetName): string
    {
        $name = $this->escapeXml($sheetName);
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $name . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            . '<font><sz val="10"/><name val="Calibri"/></font>'
            . '<font><sz val="10"/><name val="Calibri"/><b/><color rgb="FFFFFFFF"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF1F3864"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            . '</cellXfs>'
            . '</styleSheet>';
    }

    /**
     * Bangun file xlsx dan kirim langsung ke browser sebagai download
     */
    public function download(string $filename, string $sheetName = 'Data'): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $this->save($tmpFile, $sheetName);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpFile));
        header('Cache-Control: max-age=0');
        readfile($tmpFile);
        unlink($tmpFile);
    }

    /**
     * Simpan file xlsx ke path tertentu
     */
    public function save(string $path, string $sheetName = 'Data'): void
    {
        $sheetName = substr(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $sheetName), 0, 31);
        if ($sheetName === '') $sheetName = 'Data';

        $zip = new ZipArchive();
        $opened = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($opened !== true) {
            throw new RuntimeException('Tidak bisa membuat file xlsx sementara (kode error: ' . $opened . ')');
        }

        $zip->addEmptyDir('_rels');
        $zip->addEmptyDir('xl');
        $zip->addEmptyDir('xl/_rels');
        $zip->addEmptyDir('xl/worksheets');

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->buildSheetXml());

        $zip->close();
    }
}
