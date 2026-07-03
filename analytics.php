<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$user = getCurrentUser();
$uid  = $user['id'];

$total_revenue = getTotalRevenue($conn, $uid);
$total_profit  = getTotalProfit($conn, $uid);
$total_orders  = getTotalOrders($conn, $uid);
$profit_margin = $total_revenue > 0 ? round(($total_profit / $total_revenue) * 100, 1) : 0;

$revenue_by_month = getRevenueByMonth($conn, $uid);
$category_data    = getSalesByCategory($conn, $uid);
$top_products     = getTopProducts($conn, $uid, 8);

// Profit by month — single query instead of N+1 separate queries
$profit_by_month = array_fill_keys(array_keys($revenue_by_month), 0.0);
$pm_stmt = $conn->prepare(
    "SELECT month_name, COALESCE(SUM(profit), 0) as total
     FROM sales WHERE user_id = ?
     GROUP BY month_name"
);
$pm_stmt->bind_param("i", $uid);
$pm_stmt->execute();
$pm_rows = $pm_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
foreach ($pm_rows as $row) {
    if (isset($profit_by_month[$row['month_name']])) {
        $profit_by_month[$row['month_name']] = (float)$row['total'];
    }
}

// Avg order value
$avg_order = $total_orders > 0 ? round($total_revenue / $total_orders, 2) : 0;

$page_title = 'Analytics';
$extra_js = 'charts.js';
// Pass page context to charts.js so analytics charts are conditionally initialized
$page_type_js = 'analytics';
include 'includes/header.php';
?>


<div class="page-header">
  <div>
    <h1><i class='bx bx-line-chart'></i> Business Analytics</h1>
    <p>Deep-dive into your revenue, profit, and growth metrics.</p>
  </div>
  <div style="display:flex;gap:12px">
    <select class="input-field" style="width:160px">
      <option>All Time</option><option>This Month</option><option>This Quarter</option><option>This Year</option>
    </select>
    <button class="btn btn-outline" onclick="generatePagePDF('Analytics Report', [
      {label:'Total Revenue', value:'Rs.<?= number_format($total_revenue) ?>'},
      {label:'Profit Margin', value:'<?= $profit_margin ?>%'},
      {label:'Avg Order Value', value:'Rs.<?= number_format($avg_order) ?>'},
      {label:'Units Sold', value:'<?= number_format($total_orders) ?>'}
    ])"><i class='bx bx-file-blank'></i> PDF</button>
    <a href="reports.php" class="btn btn-primary">Generate Report</a>
  </div>
</div>

<!-- KPI ROW -->
<div class="kpi-grid">
  <div class="kpi-card blue"><div class="kpi-icon"><i class='bx bx-money'></i></div><div class="kpi-label">Total Revenue</div><div class="kpi-value">₹<?= number_format($total_revenue) ?></div><div class="kpi-badge up">▲ All time</div></div>
  <div class="kpi-card green"><div class="kpi-icon"><i class='bx bx-bar-chart-alt-2'></i></div><div class="kpi-label">Profit Margin</div><div class="kpi-value"><?= $profit_margin ?>%</div><div class="kpi-badge <?= $profit_margin >= 20 ? 'up':'down' ?>"><?= $profit_margin >= 20 ? '▲ Healthy':'▼ Low' ?></div></div>
  <div class="kpi-card amber"><div class="kpi-icon">🎯</div><div class="kpi-label">Avg Order Value</div><div class="kpi-value">₹<?= number_format($avg_order) ?></div><div class="kpi-badge up">▲ Per unit</div></div>
  <div class="kpi-card purple"><div class="kpi-icon"><i class='bx bx-cart'></i></div><div class="kpi-label">Total Units Sold</div><div class="kpi-value"><?= number_format($total_orders) ?></div><div class="kpi-badge up">▲ Units</div></div>
</div>

<!-- REVENUE vs PROFIT CHART -->
<div class="charts-grid two">
  <div class="card">
    <div class="card-header"><div><div class="card-title">Revenue vs Profit</div><div class="card-sub">Monthly comparison</div></div></div>
    <div style="display:flex;gap:16px;margin-bottom:12px;font-size:13px;color:var(--text-muted)">
      <span style="display:flex;align-items:center;gap:6px"><span style="width:12px;height:12px;background:#1a7fd4;border-radius:2px;display:inline-block"></span>Revenue</span>
      <span style="display:flex;align-items:center;gap:6px"><span style="width:12px;height:12px;background:#16a34a;border-radius:2px;display:inline-block"></span>Profit</span>
    </div>
    <div class="chart-wrap" style="height:260px">
      <canvas id="revProfitChart" role="img" aria-label="Revenue vs profit grouped bar chart">Revenue vs profit monthly chart.</canvas>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><div><div class="card-title">Sales by Category</div><div class="card-sub">Category share</div></div></div>
    <div class="chart-wrap" style="height:260px">
      <canvas id="analyticsPieChart" role="img" aria-label="Category distribution pie chart">Category distribution.</canvas>
    </div>
  </div>
</div>

<!-- GROWTH TREND -->
<div class="card" style="margin-bottom:24px">
  <div class="card-header"><div><div class="card-title">Revenue Growth Trend</div><div class="card-sub">Monthly revenue line chart</div></div></div>
  <div class="chart-wrap" style="height:220px">
    <canvas id="growthLineChart" role="img" aria-label="Growth trend line chart">Revenue growth trend.</canvas>
  </div>
</div>

<!-- TOP PRODUCTS TABLE -->
<div class="card">
  <div class="card-header"><div><div class="card-title">Product Performance Breakdown</div><div class="card-sub">All products by revenue & profit</div></div></div>
  <?php if(empty($top_products)): ?>
    <div class="empty-state"><div style="font-size:48px;margin-bottom:12px"><i class='bx bx-package'></i></div><p>No data. <a href="upload.php">Upload sales data</a> to see analytics.</p></div>
  <?php else: ?>
  <table class="data-table">
    <thead><tr><th>Rank</th><th>Product</th><th>Total Sales</th><th>Total Profit</th><th>Margin</th><th>Performance</th></tr></thead>
    <tbody>
      <?php
        $max_s = max(array_column($top_products, 'total_sales'));
        foreach($top_products as $i => $p):
          $m = $p['total_sales'] > 0 ? round(($p['total_profit'] / $p['total_sales']) * 100, 1) : 0;
          $pct = $max_s > 0 ? round(($p['total_sales'] / $max_s) * 100) : 0;
      ?>
      <tr>
        <td><strong>#<?= $i + 1 ?></strong></td>
        <td><?= e($p['product_name']) ?></td>
        <td>₹<?= number_format($p['total_sales']) ?></td>
        <td>₹<?= number_format($p['total_profit']) ?></td>
        <td><span class="badge <?= $m >= 20 ? 'badge-success':'badge-warning' ?>"><?= $m ?>%</span></td>
        <td style="min-width:120px"><div class="stat-bar-wrap" style="margin:0"><div class="stat-bar" style="width:<?= $pct ?>%"></div></div></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<!-- AI INSIGHTS -->
<div class="card" style="margin-top:24px">
  <div class="card-header"><div><div class="card-title">AI Business Insights <i class='bx bx-bot'></i></div><div class="card-sub">Automated analysis from your data</div></div></div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    <div class="insight-card-lg">
      <div style="font-size:24px;margin-bottom:10px"><i class='bx bx-line-chart'></i></div>
      <div style="font-weight:700;margin-bottom:6px;font-size:15px">Revenue Overview</div>
      <div style="font-size:13px;color:var(--text-muted)">Total revenue stands at <strong>₹<?= number_format($total_revenue) ?></strong> with a profit margin of <strong><?= $profit_margin ?>%</strong>. Keep uploading monthly data to track growth trends over time.</div>
    </div>
    <div class="insight-card-lg">
      <div style="font-size:24px;margin-bottom:10px">🏆</div>
      <div style="font-weight:700;margin-bottom:6px;font-size:15px">Top Category</div>
      <div style="font-size:13px;color:var(--text-muted)">
        <?php if(!empty($category_data)): ?>
          <strong><?= e($category_data[0]['category']) ?></strong> is your top performing category with ₹<?= number_format($category_data[0]['total']) ?> in sales.
        <?php else: ?>
          Upload sales data to discover your best-performing product categories.
        <?php endif; ?>
      </div>
    </div>
    <div class="insight-card-lg">
      <div style="font-size:24px;margin-bottom:10px"><i class='bx bx-bulb'></i></div>
      <div style="font-weight:700;margin-bottom:6px;font-size:15px">Profit Strategy</div>
      <div style="font-size:13px;color:var(--text-muted)">
        <?php if($profit_margin >= 20): ?>
          Your profit margin of <strong><?= $profit_margin ?>%</strong> is healthy. Focus on scaling your top products to grow revenue further.
        <?php elseif($profit_margin > 0): ?>
          Your margin of <strong><?= $profit_margin ?>%</strong> needs improvement. Review pricing and reduce operational costs to hit 20%+ margin.
        <?php else: ?>
          Add sales data to receive personalized profit strategy recommendations.
        <?php endif; ?>
      </div>
    </div>
    <div class="insight-card-lg">
      <div style="font-size:24px;margin-bottom:10px"><i class='bx bx-calendar'></i></div>
      <div style="font-weight:700;margin-bottom:6px;font-size:15px">Monthly Tip</div>
      <div style="font-size:13px;color:var(--text-muted)">Upload sales data every month to build a complete year-over-year comparison. This helps identify seasonal patterns and plan inventory better.</div>
    </div>
  </div>
</div>

<script>
// ── PHP → JS Data Bridge ──
var revenueData    = <?= json_encode(array_values($revenue_by_month)) ?>;
var revenueLabels  = <?= json_encode(array_keys($revenue_by_month)) ?>;
var profitData     = <?= json_encode(array_values($profit_by_month)) ?>;
var categoryLabels = <?= json_encode(array_column($category_data, 'category')) ?>;
var categoryValues = <?= json_encode(array_map('floatval', array_column($category_data, 'total'))) ?>;
var pageType       = 'analytics';
</script>
<?php include 'includes/footer.php'; ?>
