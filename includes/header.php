<?php
// $page_title must be set before including this file
$page_title = $page_title ?? 'BizInsight';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($page_title) ?> — BizInsight</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
  <?php if(isset($extra_css)): ?>
  <link rel="stylesheet" href="assets/css/<?= $extra_css ?>">
  <?php endif; ?>
  <style>
    body { font-family: 'Inter', 'Segoe UI', system-ui, sans-serif; }
  </style>
</head>
<body>
<!-- TOP NAV BAR -->
<header class="topbar">
  <div class="topbar-left">
    <button class="sidebar-toggle" onclick="document.body.classList.toggle('sidebar-open')" aria-label="Toggle sidebar">☰</button>
    <div class="breadcrumb">
      <a href="dashboard.php">Home</a> / <span><?= e($page_title) ?></span>
    </div>
  </div>
  <div class="topbar-right">
    <div class="topbar-search">
      <input type="text" placeholder="🔍 Search...">
    </div>
    <div class="topbar-user">
      <?php
        $display_name = $_SESSION['user_name'] ?? 'User';
        $initials     = strtoupper(substr($display_name, 0, 2));
      ?>
      <div class="topbar-avatar"><?= $initials ?></div>
      <span><?= e($display_name) ?></span>
      <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>
    </div>
  </div>
</header>

<div class="app-wrapper">
<?php include 'includes/sidebar.php'; ?>
<main class="main-content">
