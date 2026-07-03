<?php
require_once __DIR__ . '/includes/config.php';

$slug = $_GET['cat'] ?? '';
$cat = get_category($slug);

if (!$cat) {
    header('Location: index.php');
    exit;
}

// Jika sudah login, langsung lempar ke halaman data
if (is_authenticated($slug)) {
    header('Location: data.php?cat=' . urlencode($slug));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if ($password === SITE_PASSWORD) {
        set_authenticated($slug);
        header('Location: data.php?cat=' . urlencode($slug));
        exit;
    } else {
        $error = 'Password salah. Silakan coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk &middot; <?= htmlspecialchars($cat['label']) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/theme.css">
</head>
<body>
<div class="login-wrapper">
  <div class="login-card">
    <div class="login-icon-badge">
      <i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i>
    </div>
    <h4 class="mb-1 fw-bold">Akses Data: <?= htmlspecialchars($cat['label']) ?></h4>
    <p class="mb-4" style="color: var(--text-muted); font-size: 0.88rem;">
      Masukkan password untuk melanjutkan ke data kategori ini.
    </p>

    <?php if ($error): ?>
      <div class="alert-soft mb-3">
        <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <label class="form-label" style="color: var(--text-muted); font-size: 0.85rem;">Password</label>
      <div class="position-relative mb-3">
        <input type="password" name="password" id="passwordInput" class="form-control dark-input"
               placeholder="Masukkan password" required autofocus>
      </div>
      <button type="submit" class="btn-accent w-100 justify-content-center">
        <i class="bi bi-unlock"></i> Masuk
      </button>
    </form>

    <div class="text-center mt-4">
      <a href="index.php" class="breadcrumb-link">
        <i class="bi bi-arrow-left"></i> Kembali ke daftar kategori
      </a>
    </div>
  </div>
</div>
</body>
</html>
