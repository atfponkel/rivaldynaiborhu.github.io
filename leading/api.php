<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/apps_script_client.php';

header('Content-Type: application/json');

$slug = $_GET['cat'] ?? '';
$cat = get_category($slug);

if (!$cat) {
    echo json_encode(['status' => 'error', 'message' => 'Kategori tidak ditemukan']);
    exit;
}

if (!is_authenticated($slug)) {
    echo json_encode(['status' => 'error', 'message' => 'Belum login']);
    exit;
}

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $sheets = get_sheet_list($cat['url']);
    echo json_encode(['status' => 'ok', 'sheets' => $sheets]);
    exit;
}

if ($action === 'data') {
    $sheetName = $_GET['sheet'] ?? '';
    if ($sheetName === '') {
        echo json_encode(['status' => 'error', 'message' => "Parameter 'sheet' diperlukan"]);
        exit;
    }
    $data = get_sheet_data($cat['url'], $sheetName);
    echo json_encode(['status' => 'ok', 'sheet' => $sheetName, 'headers' => $data['headers'], 'rows' => $data['rows']]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Action tidak dikenali']);
