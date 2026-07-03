<?php
require_once __DIR__ . '/includes/config.php';

$slug = $_GET['cat'] ?? '';
$cat = get_category($slug);

if (!$cat) {
    header('Location: index.php');
    exit;
}

require_auth($slug);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($cat['label']) ?> &middot; Data Center</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/theme.css">
</head>
<body>
<div class="app-shell">

  <div class="topbar">
    <div class="brand">
      <span class="brand-dot"></span>
      Data Center
    </div>
    <a href="logout.php?cat=<?= urlencode($slug) ?>" class="btn-ghost">
      <i class="bi bi-box-arrow-right me-1"></i>Keluar
    </a>
  </div>

  <div class="main-content">
    <div class="page-header">
      <div>
        <a href="index.php" class="breadcrumb-link"><i class="bi bi-arrow-left"></i> Semua kategori</a>
        <h1 class="mt-1"><i class="bi <?= htmlspecialchars($cat['icon']) ?> me-2"></i><?= htmlspecialchars($cat['label']) ?></h1>
      </div>
    </div>

    <ul class="nav nav-tabs custom-tabs" id="sheetTabs" role="tablist">
      <li class="nav-item"><div class="spinner-wrap py-2"><i class="bi bi-hourglass-split"></i> Memuat daftar sheet...</div></li>
    </ul>

    <div class="data-toolbar">
      <div class="search-box">
        <i class="bi bi-search search-icon"></i>
        <input type="text" id="searchInput" placeholder="Cari nama, CIF, perusahaan, cabang, dll...">
      </div>
      <span class="row-count-pill" id="rowCountPill">0 baris</span>
      <a href="#" id="downloadBtn" class="btn-accent">
        <i class="bi bi-download"></i> Download Excel
      </a>
    </div>

    <div class="table-card">
      <div class="table-scroll">
        <div id="tableContainer">
          <div class="spinner-wrap"><i class="bi bi-hourglass-split"></i> Memuat data...</div>
        </div>
      </div>
    </div>
  </div>

  <footer class="app-footer">
    Data Center &middot; <?= htmlspecialchars($cat['label']) ?>
  </footer>
</div>

<script>
const CAT_SLUG = <?= json_encode($slug) ?>;
const CAT_LABEL = <?= json_encode($cat['label']) ?>;
</script>
<script src="assets/js/data.js"></script>
</body>
</html>
