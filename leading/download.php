<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/apps_script_client.php';
require_once __DIR__ . '/includes/SimpleXLSXWriter.php';

$slug = $_GET['cat'] ?? '';
$cat = get_category($slug);

if (!$cat) {
    http_response_code(404);
    echo 'Kategori tidak ditemukan.';
    exit;
}

if (!is_authenticated($slug)) {
    http_response_code(403);
    echo 'Anda belum login untuk kategori ini.';
    exit;
}

$sheetName = $_GET['sheet'] ?? '';
$query = trim($_GET['q'] ?? '');

if ($sheetName === '') {
    http_response_code(400);
    echo "Parameter 'sheet' diperlukan.";
    exit;
}

$data = get_sheet_data($cat['url'], $sheetName);
$headers = $data['headers'];
$rows = $data['rows'];

// Buang kolom dengan nama kosong (artifak dari Apps Script, biasanya kolom helper)
$headers = array_values(array_filter($headers, fn($h) => trim((string)$h) !== ''));

// Terapkan filter pencarian jika ada (case-insensitive, cari di semua kolom)
if ($query !== '') {
    $needle = mb_strtolower($query);
    $rows = array_values(array_filter($rows, function ($row) use ($headers, $needle) {
        foreach ($headers as $h) {
            $val = $row[$h] ?? '';
            if (mb_strpos(mb_strtolower((string)$val), $needle) !== false) {
                return true;
            }
        }
        return false;
    }));
}

$writer = new SimpleXLSXWriter();
$writer->setHeaders($headers);
$writer->setRows($rows);

$safeSheetName = preg_replace('/[^A-Za-z0-9 _-]/', '', $sheetName);
$safeSheetName = trim($safeSheetName) !== '' ? $safeSheetName : 'Data';
$filename = $cat['label'] . ' - ' . $safeSheetName . '.xlsx';

try {
    $writer->download($filename, $safeSheetName);
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Gagal membuat file Excel: ' . htmlspecialchars($e->getMessage());
}
