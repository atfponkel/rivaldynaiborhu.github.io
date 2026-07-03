<?php
/**
 * Fetch data dari sebuah URL (Google Apps Script Web App) menggunakan cURL.
 * Mengembalikan array hasil decode JSON, atau null jika gagal.
 */
function fetch_apps_script($url, $params = []) {
    if (!empty($params)) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Apps Script suka redirect
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (PHP Proxy Client)');

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['status' => 'error', 'message' => 'cURL error: ' . $error];
    }

    if ($httpCode !== 200) {
        return ['status' => 'error', 'message' => 'HTTP error: ' . $httpCode];
    }

    $data = json_decode($response, true);
    if ($data === null) {
        return ['status' => 'error', 'message' => 'Gagal parsing JSON dari Apps Script.'];
    }

    return $data;
}

/**
 * Ambil daftar nama sheet dari sebuah kategori
 */
function get_sheet_list($url) {
    $result = fetch_apps_script($url, ['action' => 'list']);
    if (!$result || $result['status'] !== 'ok') {
        return [];
    }
    return $result['sheets'] ?? [];
}

/**
 * Ambil data (headers + rows) dari sheet tertentu pada sebuah kategori
 */
function get_sheet_data($url, $sheetName) {
    $result = fetch_apps_script($url, ['action' => 'data', 'sheet' => $sheetName]);
    if (!$result || $result['status'] !== 'ok') {
        return ['headers' => [], 'rows' => []];
    }
    return [
        'headers' => $result['headers'] ?? [],
        'rows'    => $result['rows'] ?? [],
    ];
}
