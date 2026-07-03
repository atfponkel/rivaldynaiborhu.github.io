<?php
session_start();

// ============================================
// KONFIGURASI UTAMA
// ============================================

// Password untuk masuk ke setiap menu (sama untuk semua)
define('SITE_PASSWORD', 'Ponkelaku');

// Daftar kategori/menu dan URL Web App Google Apps Script masing-masing
$CATEGORIES = [
    'axa' => [
        'label' => 'AXA',
        'url'   => 'https://script.google.com/macros/s/AKfycbw71iFt8TIJnI3hc93Wn8oxA_lqN6_n6fnkKfSjUcw2rB0C1sX3cKiP87lneViElcI46g/exec',
        'icon'  => 'bi-shield-check',
    ],
    'retail-funding' => [
        'label' => 'Retail Funding',
        'url'   => 'https://script.google.com/macros/s/AKfycbz4z5lCTH2IML6LX60HwXu6kyfRGfVQpjMq7NHl2M6uYoQ8xbX413lpfn2NXIQBni8G/exec',
        'icon'  => 'bi-piggy-bank',
    ],
    'tbr' => [
        'label' => 'TBR',
        'url'   => 'https://script.google.com/macros/s/AKfycbzhzTSnrz4P_eFhlaeBbQzaadA2LDnAoO55lg_erSa83EEW_3qpiAk6MsVWsU03nIMH/exec',
        'icon'  => 'bi-bank',
    ],
    'tbw' => [
        'label' => 'TBW',
        'url'   => 'https://script.google.com/macros/s/AKfycbw33cFTkBuXmS426DjLGgGRFWnUVSi697ENKwpcS2nB_KIl4kyfRp4JzXycMNd0fiNkJw/exec',
        'icon'  => 'bi-bank2',
    ],
    'wealth' => [
        'label' => 'Wealth',
        'url'   => 'https://script.google.com/macros/s/AKfycbwie7UUw0wxRkItQJoQQ91fKD11cAgwMgOm6C3QetGJojsJmNl7Lw9nS0ttVneru1ljIA/exec',
        'icon'  => 'bi-gem',
    ],
];

/**
 * Ambil konfigurasi kategori berdasarkan slug, atau null jika tidak ditemukan
 */
function get_category($slug) {
    global $CATEGORIES;
    return $CATEGORIES[$slug] ?? null;
}

/**
 * Cek apakah user sudah login (punya akses) untuk kategori tertentu
 */
function is_authenticated($slug) {
    return isset($_SESSION['auth'][$slug]) && $_SESSION['auth'][$slug] === true;
}

/**
 * Set status login untuk kategori tertentu
 */
function set_authenticated($slug) {
    $_SESSION['auth'][$slug] = true;
}

/**
 * Wajibkan login, redirect ke halaman login jika belum
 */
function require_auth($slug) {
    if (!is_authenticated($slug)) {
        header('Location: login.php?cat=' . urlencode($slug));
        exit;
    }
}
