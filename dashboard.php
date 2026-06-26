<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$user = getCurrentUser();
$uid  = $user['id'];

// Period filter
$period = $_GET['period'] ?? 'all';
$valid_periods = ['all', 'month', 'quarter', 'year'];
if (!in_array($period, $valid_periods)) $period = 'all';

// Build date condition for the period
$period_sql = '';
$period_params = [$uid];
$period_types  = 'i';
if ($period === 'month') {
    $period_sql = " AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())";
} elseif ($period === 'quarter') {
    $period_sql = " AND QUARTER(created_at) = QUARTER(NOW()) AND YEAR(created_at) = YEAR(NOW())";
} elseif ($period === 'year') {
    $period_sql = " AND YEAR(created_at) = YEAR(NOW())";
}

// Fetch KPIs (period-aware)
function getPeriodSum($conn, $uid, $col, $period_sql) {
    // Whitelist allowed column names to prevent any injection
    $allowed_cols = ['sales_amount', 'profit'];
    if (!in_array($col, $allowed_cols, true)) return 0;
    $stmt = $conn->prepare("SELECT COALESCE(SUM($col), 0) as total FROM sales WHERE user_id = ? $period_sql");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    return (float)$stmt->get_result()->fetch_assoc()['total'];
}
function getPeriodCount($conn, $uid, $col, $period_sql) {
    // Whitelist allowed column names to prevent any injection
    $allowed_cols = ['quantity'];
    if (!in_array($col, $allowed_cols, true)) return 0;
    $stmt = $conn->prepare("SELECT COALESCE(SUM($col), 0) as total FROM sales WHERE user_id = ? $period_sql");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    return (int)$stmt->get_result()->fetch_assoc()['total'];
}

$total_revenue = getPeriodSum($conn, $uid, 'sales_amount', $period_sql);
$total_profit  = getPeriodSum($conn, $uid, 'profit', $period_sql);
$total_orders  = getPeriodCount($conn, $uid, 'quantity', $period_sql);
$profit_margin = $total_revenue > 0 ? round(($total_profit / $total_revenue) * 100, 1) : 0;

// Revenue by month for bar chart (always all-time for chart clarity)
$revenue_by_month = getRevenueByMonth($conn, $uid);

// Category data for pie chart (period-aware)
$stmt = $conn->prepare("SELECT category, SUM(sales_amount) as total FROM sales WHERE user_id = ? $period_sql GROUP BY category ORDER BY total DESC");
$stmt->bind_param("i", $uid);
$stmt->execute();
$category_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


// Top products (period-aware)
$stmt = $conn->prepare("SELECT product_name, SUM(sales_amount) as total_sales, SUM(profit) as total_profit FROM sales WHERE user_id = ? $period_sql GROUP BY product_name ORDER BY total_sales DESC LIMIT 5");
$stmt->bind_param("i", $uid);
$stmt->execute();
$top_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Recent sales (period-aware)
$stmt = $conn->prepare("SELECT * FROM sales WHERE user_id = ? $period_sql ORDER BY created_at DESC LIMIT 8");
$stmt->bind_param("i", $uid);
$stmt->execute();
$recent_sales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch user's business name
$bstmt = $conn->prepare("SELECT business_name FROM users WHERE id = ?");
$bstmt->bind_param("i", $uid);
$bstmt->execute();
$biz = $bstmt->get_result()->fetch_assoc();

$page_title = 'Dashboard';
$extra_js = 'charts.js';
include 'includes/header.php';
?>

<div class="page-header">
  <div>
    <h1>📊 Business Dashboard</h1>
    <p>Welcome back, <strong><?= e($user['name']) ?></strong>! Here's your business performance overview for <?= e($biz['business_name'] ?? 'your business') ?>.</p>
  </div>
  <div style="display:flex;gap:12px;align-items:center">
    <select class="input-field" style="width:160px" id="dashPeriod" onchange="window.location.href='dashboard.php?period='+this.value">
      <option value="all"   <?= $period==='all'     ? 'selected' : '' ?>>All Time</option>
      <option value="month" <?= $period==='month'   ? 'selected' : '' ?>>This Month</option>
      <option value="quarter" <?= $period==='quarter' ? 'selected' : '' ?>>This Quarter</option>
      <option value="year"  <?= $period==='year'    ? 'selected' : '' ?>>This Year</option>
    </select>
    <button class="btn btn-outline" onclick="generatePagePDF('Dashboard Report', [
      {label:'Total Revenue', value:'Rs.<?= number_format($total_revenue) ?>'},
      {label:'Total Profit', value:'Rs.<?= number_format($total_profit) ?>'},
      {label:'Units Sold', value:'<?= number_format($total_orders) ?>'},
      {label:'Profit Margin', value:'<?= $profit_margin ?>%'}
    ])">📄 PDF</button>
    <a href="upload.php" class="btn btn-primary">+ Upload Data</a>
  </div>
</div>

<!-- KPI CARDS -->
<div class="kpi-grid">
  <div class="kpi-card blue">
    <div class="kpi-icon">💰</div>
    <div class="kpi-label">Total Revenue</div>
    <div class="kpi-value">₹<?= number_format($total_revenue) ?></div>
    <div class="kpi-badge up">▲ Business Revenue</div>
  </div>
  <div class="kpi-card green">
    <div class="kpi-icon">📈</div>
    <div class="kpi-label">Total Profit</div>
    <div class="kpi-value">₹<?= number_format($total_profit) ?></div>
    <div class="kpi-badge up">▲ Net Profit</div>
  </div>
  <div class="kpi-card amber">
    <div class="kpi-icon">🛒</div>
    <div class="kpi-label">Total Orders</div>
    <div class="kpi-value"><?= number_format($total_orders) ?></div>
    <div class="kpi-badge up">▲ Units Sold</div>
  </div>
  <div class="kpi-card purple">
    <div class="kpi-icon">🎯</div>
    <div class="kpi-label">Profit Margin</div>
    <div class="kpi-value"><?= $profit_margin ?>%</div>
    <div class="kpi-badge <?= $profit_margin >= 20 ? 'up' : 'down' ?>">
      <?= $profit_margin >= 20 ? '▲ Healthy' : '▼ Needs attention' ?>
    </div>
  </div>
</div>

<!-- CHARTS ROW 1 -->
<div class="charts-grid two">
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Monthly Revenue</div>
        <div class="card-sub">Revenue trend across all months</div>
      </div>
      <a href="reports.php" class="btn btn-outline btn-sm">View Reports</a>
    </div>
    <div class="chart-wrap" style="height:250px">
      <canvas id="revenueBarChart" role="img" aria-label="Monthly revenue bar chart">Monthly revenue data.</canvas>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Sales by Category</div>
        <div class="card-sub">Product category distribution</div>
      </div>
    </div>
    <div class="chart-wrap" style="height:250px">
      <canvas id="categoryPieChart" role="img" aria-label="Category distribution pie chart">Category distribution.</canvas>
    </div>
  </div>
</div>

<!-- TOP PRODUCTS + RECENT SALES -->
<div class="charts-grid two">
  <div class="card">
    <div class="card-header">
      <div><div class="card-title">Top Products</div><div class="card-sub">By total revenue</div></div>
      <a href="sales.php" class="btn btn-outline btn-sm">All Sales</a>
    </div>
    <?php if(empty($top_products)): ?>
      <div class="empty-state">
        <div style="font-size:48px;margin-bottom:12px">📦</div>
        <p>No sales data yet. <a href="upload.php">Upload your first file</a></p>
      </div>
    <?php else: ?>
      <?php
        $max_sales = max(array_column($top_products, 'total_sales'));
        foreach($top_products as $p):
          $pct = $max_sales > 0 ? round(($p['total_sales'] / $max_sales) * 100) : 0;
      ?>
      <div class="stat-row">
        <span class="stat-name"><?= e($p['product_name']) ?></span>
        <div class="stat-bar-wrap"><div class="stat-bar" style="width:<?= $pct ?>%"></div></div>
        <span class="stat-val">₹<?= number_format($p['total_sales']) ?></span>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-header">
      <div><div class="card-title">AI Insights 🤖</div><div class="card-sub">Automated analysis</div></div>
    </div>
    <?php
      $insights = [];
      if($total_revenue > 0) {
        $insights[] = ['icon'=>'📈','title'=>'Revenue Active','body'=>'Your total revenue is ₹'.number_format($total_revenue).'. Keep uploading monthly data to track growth trends.'];
      }
      if($profit_margin >= 20) {
        $insights[] = ['icon'=>'✅','title'=>'Healthy Margin','body'=>'Profit margin of '.$profit_margin.'% is above the 20% benchmark — great job!'];
      } elseif($profit_margin > 0) {
        $insights[] = ['icon'=>'⚠️','title'=>'Improve Margin','body'=>'Profit margin of '.$profit_margin.'% is below 20%. Review expenses and pricing strategy.'];
      }
      if(!empty($top_products)) {
        $top = $top_products[0];
        $insights[] = ['icon'=>'🏆','title'=>'Top Performer','body'=>e($top['product_name']).' is your top product generating ₹'.number_format($top['total_sales']).' in sales.'];
      }
      if(empty($insights)) {
        $insights[] = ['icon'=>'💡','title'=>'Get Started','body'=>'Upload your sales data using CSV or Excel to see AI-powered business insights here.'];
      }
    ?>
    <div style="display:flex;flex-direction:column;gap:12px">
      <?php foreach($insights as $insight): ?>
      <div class="insight-card">
        <div class="insight-icon"><?= $insight['icon'] ?></div>
        <div>
          <div class="insight-title"><?= $insight['title'] ?></div>
          <div class="insight-body"><?= $insight['body'] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- RECENT TRANSACTIONS TABLE -->
<div class="card">
  <div class="card-header">
    <div><div class="card-title">Recent Transactions</div><div class="card-sub">Latest sales activity</div></div>
    <a href="sales.php" class="btn btn-outline btn-sm">View All</a>
  </div>
  <?php if(empty($recent_sales)): ?>
    <div class="empty-state">
      <div style="font-size:48px;margin-bottom:12px">🛒</div>
      <p>No sales records yet. <a href="sales.php">Add your first sale</a> or <a href="upload.php">upload a CSV file</a>.</p>
    </div>
  <?php else: ?>
  <table class="data-table">
    <thead>
      <tr><th>#</th><th>Product</th><th>Category</th><th>Qty</th><th>Sales</th><th>Profit</th><th>Month</th><th>Date Added</th></tr>
    </thead>
    <tbody>
      <?php foreach($recent_sales as $s): ?>
      <tr>
        <td><?= $s['id'] ?></td>
        <td><strong><?= e($s['product_name']) ?></strong></td>
        <td><span class="badge badge-info"><?= e($s['category']) ?></span></td>
        <td><?= number_format($s['quantity']) ?></td>
        <td>₹<?= number_format($s['sales_amount']) ?></td>
        <td>₹<?= number_format($s['profit']) ?></td>
        <td><?= e($s['month_name']) ?></td>
        <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<!-- Chart Data (passed to JS) -->
<script>
// ── PHP → JS Data Bridge ──
var revenueData    = <?= json_encode(array_values($revenue_by_month)) ?>;
var revenueLabels  = <?= json_encode(array_keys($revenue_by_month)) ?>;
var categoryLabels = <?= json_encode(array_column($category_data, 'category')) ?>;
var categoryValues = <?= json_encode(array_map('floatval', array_column($category_data, 'total'))) ?>;
var pageType       = 'dashboard';
</script>

<?php include 'includes/footer.php'; ?>
