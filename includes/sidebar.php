<?php
// Determine current page for active link highlighting
$current = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <a href="dashboard.php" class="sidebar-logo"><i class='bx bx-bar-chart-alt-2'></i> BizInsight</a>
    <div class="sidebar-tagline">Analytics Platform</div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="dashboard.php" class="nav-item <?= $current === 'dashboard' ? 'active' : '' ?>">
      <span class="nav-icon"><i class='bx bx-home-circle'></i></span> Dashboard
    </a>
    <a href="analytics.php" class="nav-item <?= $current === 'analytics' ? 'active' : '' ?>">
      <span class="nav-icon"><i class='bx bx-line-chart'></i></span> Analytics
    </a>
    <a href="sales.php" class="nav-item <?= $current === 'sales' ? 'active' : '' ?>">
      <span class="nav-icon"><i class='bx bx-cart'></i></span> Sales Data
    </a>

    <div class="nav-section-label">Reports</div>
    <a href="reports.php" class="nav-item <?= $current === 'reports' ? 'active' : '' ?>">
      <span class="nav-icon"><i class='bx bxs-report'></i></span> Reports
    </a>
    <a href="upload.php" class="nav-item <?= $current === 'upload' ? 'active' : '' ?>">
      <span class="nav-icon"><i class='bx bx-folder-open'></i></span> Upload Data
    </a>

    <div class="nav-section-label">Account</div>
    <a href="settings.php" class="nav-item <?= $current === 'settings' ? 'active' : '' ?>">
      <span class="nav-icon"><i class='bx bx-cog'></i></span> Settings
    </a>
    <a href="logout.php" class="nav-item logout">
      <span class="nav-icon"><i class='bx bx-log-out'></i></span> Logout
    </a>
  </nav>

  <div class="sidebar-user">
    <?php $sidebar_name = $_SESSION['user_name'] ?? 'User'; ?>
    <div class="user-avatar"><?= strtoupper(substr($sidebar_name, 0, 2)) ?></div>
    <div class="user-info">
      <div class="user-name"><?= e($sidebar_name) ?></div>
      <div class="user-role">Business Owner</div>
    </div>
  </div>
</aside>
