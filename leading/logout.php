<?php
require_once __DIR__ . '/includes/config.php';

$slug = $_GET['cat'] ?? '';

if ($slug && isset($_SESSION['auth'][$slug])) {
    unset($_SESSION['auth'][$slug]);
}

header('Location: index.php');
exit;
