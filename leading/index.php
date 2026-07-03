<?php
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Center | Dashboard</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
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
    <div class="text-secondary small d-none d-sm-block" style="color: var(--text-muted);">
      <i class="bi bi-shield-lock me-1"></i>Akses dilindungi per kategori
    </div>
  </div>

  <div class="main-content">
    <div class="page-header">
      <div>
        <h1>Pilih Kategori Data</h1>
        <p class="mb-0" style="color: var(--text-muted); font-size: 0.92rem;">
          Klik salah satu kategori di bawah untuk melihat dan mencari data.
        </p>
      </div>
    </div>

    <div class="category-grid">
      <?php foreach ($CATEGORIES as $slug => $cat): ?>
        <a href="login.php?cat=<?= urlencode($slug) ?>" class="category-card">
          <div class="icon-wrap"><i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i></div>
          <div class="cat-title"><?= htmlspecialchars($cat['label']) ?></div>
          <div class="cat-sub">Lihat data &amp; cari record</div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <footer class="app-footer">
    Data Center &middot; akses internal
  </footer>
</div>
</body>
</html>
