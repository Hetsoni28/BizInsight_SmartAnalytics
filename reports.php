<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$user = getCurrentUser();
$uid  = $user['id'];

// Handle CSV exports (type-specific)
if(isset($_GET['type']) && str_ends_with($_GET['type'], '_csv')) {
    $csv_type = $_GET['type'];
    header('Content-Type: text/csv; charset=utf-8');
    $out = fopen('php://output', 'w');

    $months_all = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    switch($csv_type) {

        case 'sales_csv':
            header('Content-Disposition: attachment; filename="Sales_Report_' . date('Y_m_d') . '.csv"');
            $stmt = $conn->prepare("SELECT product_name, category, sales_amount, profit, quantity, month_name, created_at FROM sales WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->bind_param("i", $uid);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            fputcsv($out, ['Product Name', 'Category', 'Sales Amount', 'Profit', 'Quantity', 'Month', 'Date Added']);
            foreach($rows as $r) fputcsv($out, [$r['product_name'], $r['category'], $r['sales_amount'], $r['profit'], $r['quantity'], $r['month_name'], $r['created_at']]);
            break;

        case 'revenue_csv':
            header('Content-Disposition: attachment; filename="Revenue_Report_' . date('Y_m_d') . '.csv"');
            // Revenue by month
            fputcsv($out, ['Month', 'Revenue']);
            foreach($months_all as $m) {
                $stmt = $conn->prepare("SELECT COALESCE(SUM(sales_amount),0) as total FROM sales WHERE user_id = ? AND month_name = ?");
                $stmt->bind_param("is", $uid, $m);
                $stmt->execute();
                $rev = $stmt->get_result()->fetch_assoc()['total'];
                if($rev > 0) fputcsv($out, [$m, $rev]);
            }
            fputcsv($out, []);
            // Revenue by category
            fputcsv($out, ['Category', 'Revenue', 'Profit', 'Margin %']);
            $stmt = $conn->prepare("SELECT category, SUM(sales_amount) as total_sales, SUM(profit) as total_profit FROM sales WHERE user_id = ? GROUP BY category ORDER BY total_sales DESC");
            $stmt->bind_param("i", $uid);
            $stmt->execute();
            $cats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            foreach($cats as $c) {
                $margin = $c['total_sales'] > 0 ? round(($c['total_profit']/$c['total_sales'])*100,1) : 0;
                fputcsv($out, [$c['category'], $c['total_sales'], $c['total_profit'], $margin.'%']);
            }
            break;

        case 'product_csv':
            header('Content-Disposition: attachment; filename="Product_Performance_' . date('Y_m_d') . '.csv"');
            fputcsv($out, ['Rank', 'Product Name', 'Total Sales', 'Total Profit', 'Quantity', 'Margin %']);
            $stmt = $conn->prepare("SELECT product_name, SUM(sales_amount) as total_sales, SUM(profit) as total_profit, SUM(quantity) as total_qty FROM sales WHERE user_id = ? GROUP BY product_name ORDER BY total_sales DESC");
            $stmt->bind_param("i", $uid);
            $stmt->execute();
            $prods = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            foreach($prods as $i => $p) {
                $m = $p['total_sales'] > 0 ? round(($p['total_profit']/$p['total_sales'])*100,1) : 0;
                fputcsv($out, [$i+1, $p['product_name'], $p['total_sales'], $p['total_profit'], $p['total_qty'], $m.'%']);
            }
            break;

        case 'profit_csv':
            header('Content-Disposition: attachment; filename="Profit_Loss_Report_' . date('Y_m_d') . '.csv"');
            // Monthly P&L
            fputcsv($out, ['--- Monthly Profit & Loss ---']);
            fputcsv($out, ['Month', 'Revenue', 'Profit', 'Margin %', 'Status']);
            foreach($months_all as $m) {
                $stmt = $conn->prepare("SELECT COALESCE(SUM(sales_amount),0) as rev, COALESCE(SUM(profit),0) as prof FROM sales WHERE user_id = ? AND month_name = ?");
                $stmt->bind_param("is", $uid, $m);
                $stmt->execute();
                $r = $stmt->get_result()->fetch_assoc();
                if($r['rev'] > 0 || $r['prof'] != 0) {
                    $mg = $r['rev'] > 0 ? round(($r['prof']/$r['rev'])*100,1) : 0;
                    $status = $r['prof'] <= 0 ? 'LOSS' : ($mg < 10 ? 'LOW MARGIN' : 'HEALTHY');
                    fputcsv($out, [$m, $r['rev'], $r['prof'], $mg.'%', $status]);
                }
            }
            fputcsv($out, []);
            // Loss products
            fputcsv($out, ['--- Loss / Low-Margin Products ---']);
            fputcsv($out, ['Product', 'Category', 'Sales', 'Profit', 'Margin %', 'Status']);
            $stmt = $conn->prepare("SELECT product_name, category, SUM(sales_amount) as ts, SUM(profit) as tp FROM sales WHERE user_id = ? GROUP BY product_name, category HAVING tp <= 0 OR (ts > 0 AND (tp/ts)*100 < 5) ORDER BY tp ASC LIMIT 15");
            $stmt->bind_param("i", $uid);
            $stmt->execute();
            $lp = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            foreach($lp as $p) {
                $mg = $p['ts'] > 0 ? round(($p['tp']/$p['ts'])*100,1) : 0;
                $st = $p['tp'] <= 0 ? 'LOSS' : 'LOW MARGIN';
                fputcsv($out, [$p['product_name'], $p['category'], $p['ts'], $p['tp'], $mg.'%', $st]);
            }
            break;

        case 'growth_csv':
            header('Content-Disposition: attachment; filename="Growth_Report_' . date('Y_m_d') . '.csv"');
            fputcsv($out, ['Month', 'Revenue', 'Previous Month Revenue', 'Growth Amount', 'Growth %']);
            $prev = 0;
            foreach($months_all as $m) {
                $stmt = $conn->prepare("SELECT COALESCE(SUM(sales_amount),0) as total FROM sales WHERE user_id = ? AND month_name = ?");
                $stmt->bind_param("is", $uid, $m);
                $stmt->execute();
                $rev = (float)$stmt->get_result()->fetch_assoc()['total'];
                if($rev > 0 || $prev > 0) {
                    $growth_amt = $rev - $prev;
                    $growth_pct = $prev > 0 ? round(($growth_amt / $prev) * 100, 1) : '-';
                    fputcsv($out, [$m, $rev, $prev, $growth_amt, is_numeric($growth_pct) ? $growth_pct.'%' : $growth_pct]);
                }
                if($rev > 0) $prev = $rev;
            }
            break;

        case 'category_csv':
            header('Content-Disposition: attachment; filename="Category_Analytics_' . date('Y_m_d') . '.csv"');
            fputcsv($out, ['Category', 'Total Sales', 'Total Profit', 'Units Sold', 'Margin %', 'Revenue Share %']);
            $stmt = $conn->prepare("SELECT category, SUM(sales_amount) as ts, SUM(profit) as tp, SUM(quantity) as tq FROM sales WHERE user_id = ? GROUP BY category ORDER BY ts DESC");
            $stmt->bind_param("i", $uid);
            $stmt->execute();
            $cats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $grand = array_sum(array_column($cats, 'ts'));
            foreach($cats as $c) {
                $mg = $c['ts'] > 0 ? round(($c['tp']/$c['ts'])*100,1) : 0;
                $share = $grand > 0 ? round(($c['ts']/$grand)*100,1) : 0;
                fputcsv($out, [$c['category'], $c['ts'], $c['tp'], $c['tq'], $mg.'%', $share.'%']);
            }
            break;

        default:
            header('Content-Disposition: attachment; filename="report_' . date('Y_m_d') . '.csv"');
            fputcsv($out, ['No data available for this report type.']);
    }

    fclose($out);
    exit();
}

// Summary stats
$total_revenue = getTotalRevenue($conn, $uid);
$total_profit  = getTotalProfit($conn, $uid);
$total_orders  = getTotalOrders($conn, $uid);
$category_data = getSalesByCategory($conn, $uid);
$top_products  = getTopProducts($conn, $uid, 10);
$revenue_by_month = getRevenueByMonth($conn, $uid);

// Profit by month
$profit_by_month = array_fill_keys(array_keys($revenue_by_month), 0.0);
$pm_stmt = $conn->prepare("SELECT month_name, COALESCE(SUM(profit), 0) as total FROM sales WHERE user_id = ? GROUP BY month_name");
$pm_stmt->bind_param("i", $uid);
$pm_stmt->execute();
$pm_rows = $pm_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
foreach ($pm_rows as $row) {
    if (isset($profit_by_month[$row['month_name']])) {
        $profit_by_month[$row['month_name']] = (float)$row['total'];
    }
}

// Quantity by month
$qty_by_month = array_fill_keys(array_keys($revenue_by_month), 0);
$qm_stmt = $conn->prepare("SELECT month_name, COALESCE(SUM(quantity), 0) as total FROM sales WHERE user_id = ? GROUP BY month_name");
$qm_stmt->bind_param("i", $uid);
$qm_stmt->execute();
$qm_rows = $qm_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
foreach ($qm_rows as $row) {
    if (isset($qty_by_month[$row['month_name']])) {
        $qty_by_month[$row['month_name']] = (int)$row['total'];
    }
}

// Category profit data
$cat_profit_data = [];
$cp_stmt = $conn->prepare("SELECT category, SUM(sales_amount) as total_sales, SUM(profit) as total_profit, SUM(quantity) as total_qty FROM sales WHERE user_id = ? GROUP BY category ORDER BY total_sales DESC");
$cp_stmt->bind_param("i", $uid);
$cp_stmt->execute();
$cat_profit_data = $cp_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Loss-making products (profit <= 0 or margin < 5%)
$loss_stmt = $conn->prepare(
    "SELECT product_name, category, SUM(sales_amount) as total_sales, SUM(profit) as total_profit, SUM(quantity) as total_qty
     FROM sales WHERE user_id = ?
     GROUP BY product_name, category
     HAVING total_profit <= 0 OR (total_sales > 0 AND (total_profit / total_sales) * 100 < 5)
     ORDER BY total_profit ASC
     LIMIT 15"
);
$loss_stmt->bind_param("i", $uid);
$loss_stmt->execute();
$loss_products = $loss_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Loss-making categories
$loss_cat_stmt = $conn->prepare(
    "SELECT category, SUM(sales_amount) as total_sales, SUM(profit) as total_profit, SUM(quantity) as total_qty
     FROM sales WHERE user_id = ?
     GROUP BY category
     HAVING total_profit <= 0 OR (total_sales > 0 AND (total_profit / total_sales) * 100 < 10)
     ORDER BY total_profit ASC"
);
$loss_cat_stmt->bind_param("i", $uid);
$loss_cat_stmt->execute();
$loss_categories = $loss_cat_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Total loss calculation
$total_loss = 0;
$loss_item_count = 0;
$lowest_margin = 999;
foreach($loss_products as $lp) {
    if($lp['total_profit'] < 0) {
        $total_loss += abs($lp['total_profit']);
    }
    $loss_item_count++;
    $lm = $lp['total_sales'] > 0 ? round(($lp['total_profit']/$lp['total_sales'])*100,1) : 0;
    if($lm < $lowest_margin) $lowest_margin = $lm;
}
if($lowest_margin == 999) $lowest_margin = 0;

// Months with loss
$loss_months = [];
foreach($revenue_by_month as $month => $rev) {
    $prof = $profit_by_month[$month] ?? 0;
    if($rev > 0 && $prof <= 0) {
        $loss_months[] = ['month' => $month, 'revenue' => $rev, 'profit' => $prof];
    }
}

// Recent uploads
$upstmt = $conn->prepare("SELECT * FROM uploads WHERE user_id=? ORDER BY uploaded_at DESC LIMIT 10");
$upstmt->bind_param("i", $uid);
$upstmt->execute();
$upload_list = $upstmt->get_result()->fetch_all(MYSQLI_ASSOC);

$months_list = ['January','February','March','April','May','June','July','August','September','October','November','December'];

$page_title = 'Reports';
include 'includes/header.php';
?>

<div class="page-header">
  <div>
    <h1>📑 Business Reports</h1>
    <p>Generate and download professional business reports from your data.</p>
  </div>
  <a href="reports.php?type=sales_csv" class="btn btn-primary">📥 Export Sales CSV</a>
</div>

<!-- REPORT TYPE CARDS -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:28px" class="report-grid">

  <div class="report-card" onclick="showReport('sales')">
    <div class="report-icon">📊</div>
    <div class="report-title">Monthly Sales Report</div>
    <div class="report-desc">Month-by-month sales breakdown with totals and comparisons.</div>
    <div class="report-actions">
      <a href="reports.php?type=sales_csv" class="btn btn-primary btn-sm" onclick="event.stopPropagation()">CSV</a>
      <button class="btn btn-outline btn-sm" onclick="event.stopPropagation();generatePDF('sales')">PDF</button>
    </div>
  </div>

  <div class="report-card" onclick="showReport('revenue')">
    <div class="report-icon">💰</div>
    <div class="report-title">Revenue Report</div>
    <div class="report-desc">Total revenue analysis with growth trends and category breakdown.</div>
    <div class="report-actions">
      <a href="reports.php?type=revenue_csv" class="btn btn-primary btn-sm" onclick="event.stopPropagation()">CSV</a>
      <button class="btn btn-outline btn-sm" onclick="event.stopPropagation();generatePDF('revenue')">PDF</button>
    </div>
  </div>

  <div class="report-card" onclick="showReport('product')">
    <div class="report-icon">📦</div>
    <div class="report-title">Product Performance</div>
    <div class="report-desc">Top products, lowest performers, and category-wise analysis.</div>
    <div class="report-actions">
      <a href="reports.php?type=product_csv" class="btn btn-primary btn-sm" onclick="event.stopPropagation()">CSV</a>
      <button class="btn btn-outline btn-sm" onclick="event.stopPropagation();generatePDF('product')">PDF</button>
    </div>
  </div>

  <div class="report-card" onclick="showReport('profit')">
    <div class="report-icon">📉</div>
    <div class="report-title">Profit / Loss Report</div>
    <div class="report-desc">Detailed P&L analysis with margin and expense breakdown.</div>
    <div class="report-actions">
      <a href="reports.php?type=profit_csv" class="btn btn-primary btn-sm" onclick="event.stopPropagation()">CSV</a>
      <button class="btn btn-outline btn-sm" onclick="event.stopPropagation();generatePDF('profit')">PDF</button>
    </div>
  </div>

  <div class="report-card" onclick="showReport('growth')">
    <div class="report-icon">📅</div>
    <div class="report-title">Monthly Growth Report</div>
    <div class="report-desc">Month-over-month growth analysis with trend indicators.</div>
    <div class="report-actions">
      <a href="reports.php?type=growth_csv" class="btn btn-primary btn-sm" onclick="event.stopPropagation()">CSV</a>
      <button class="btn btn-outline btn-sm" onclick="event.stopPropagation();generatePDF('growth')">PDF</button>
    </div>
  </div>

  <div class="report-card" onclick="showReport('category')">
    <div class="report-icon">🗂️</div>
    <div class="report-title">Category Analytics</div>
    <div class="report-desc">Sales and profit breakdown by product category.</div>
    <div class="report-actions">
      <a href="reports.php?type=category_csv" class="btn btn-primary btn-sm" onclick="event.stopPropagation()">CSV</a>
      <button class="btn btn-outline btn-sm" onclick="event.stopPropagation();generatePDF('category')">PDF</button>
    </div>
  </div>
</div>

<!-- LIVE REPORT PREVIEW SECTION -->
<div class="card" id="report-preview">
  <div class="card-header">
    <div><div class="card-title" id="report-preview-title">📊 Sales Summary Report</div><div class="card-sub">Click any report card above to preview</div></div>
    <button class="btn btn-outline btn-sm" onclick="generatePDF()">📄 Download PDF</button>
  </div>

  <!-- Summary Stats -->
  <div class="kpi-grid" style="margin-bottom:24px">
    <div class="kpi-card blue" style="padding:16px"><div class="kpi-label">Total Revenue</div><div class="kpi-value" style="font-size:22px">₹<?= number_format($total_revenue) ?></div></div>
    <div class="kpi-card green" style="padding:16px"><div class="kpi-label">Total Profit</div><div class="kpi-value" style="font-size:22px">₹<?= number_format($total_profit) ?></div></div>
    <div class="kpi-card amber" style="padding:16px"><div class="kpi-label">Units Sold</div><div class="kpi-value" style="font-size:22px"><?= number_format($total_orders) ?></div></div>
    <div class="kpi-card purple" style="padding:16px"><div class="kpi-label">Profit Margin</div><div class="kpi-value" style="font-size:22px"><?= $total_revenue > 0 ? round(($total_profit/$total_revenue)*100,1) : 0 ?>%</div></div>
  </div>

  <!-- Category Breakdown -->
  <?php if(!empty($category_data)): ?>
  <div style="margin-bottom:24px">
    <div style="font-weight:700;margin-bottom:16px;font-size:15px">Category Breakdown</div>
    <table class="data-table">
      <thead><tr><th>Category</th><th>Total Sales</th><th>% Share</th></tr></thead>
      <tbody>
        <?php foreach($category_data as $c): $share = $total_revenue > 0 ? round(($c['total'] / $total_revenue) * 100, 1) : 0; ?>
        <tr>
          <td><strong><?= e($c['category']) ?></strong></td>
          <td>₹<?= number_format($c['total']) ?></td>
          <td><div style="display:flex;align-items:center;gap:8px"><div class="stat-bar-wrap" style="width:80px;margin:0"><div class="stat-bar" style="width:<?= $share ?>%"></div></div><?= $share ?>%</div></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <!-- Top Products -->
  <?php if(!empty($top_products)): ?>
  <div style="margin-bottom:24px">
    <div style="font-weight:700;margin-bottom:16px;font-size:15px">Top 10 Products</div>
    <table class="data-table">
      <thead><tr><th>Rank</th><th>Product</th><th>Total Sales</th><th>Total Profit</th><th>Margin</th></tr></thead>
      <tbody>
        <?php foreach($top_products as $i => $p): $m = $p['total_sales'] > 0 ? round(($p['total_profit']/$p['total_sales'])*100,1) : 0; ?>
        <tr>
          <td>#<?= $i+1 ?></td>
          <td><strong><?= e($p['product_name']) ?></strong></td>
          <td>₹<?= number_format($p['total_sales']) ?></td>
          <td>₹<?= number_format($p['total_profit']) ?></td>
          <td><span class="badge <?= $m >= 20 ? 'badge-success': ($m < 5 ? 'badge-danger':'badge-warning') ?>"><?= $m ?>%</span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <!-- Loss Analysis Section -->
  <?php if(!empty($loss_products)): ?>
  <div style="margin-bottom:24px">
    <div style="font-weight:700;margin-bottom:16px;font-size:15px;color:var(--red)">⚠️ Loss / Low-Margin Analysis</div>
    <div class="kpi-grid" style="margin-bottom:20px;grid-template-columns:repeat(3,1fr)">
      <div class="kpi-card" style="padding:16px;border-color:#fecaca;background:#fff5f5">
        <div class="kpi-label" style="color:#991b1b">Total Loss Amount</div>
        <div class="kpi-value" style="font-size:20px;color:#dc2626">₹<?= number_format($total_loss) ?></div>
      </div>
      <div class="kpi-card" style="padding:16px;border-color:#fecaca;background:#fff5f5">
        <div class="kpi-label" style="color:#991b1b">Loss / Low-Margin Items</div>
        <div class="kpi-value" style="font-size:20px;color:#dc2626"><?= $loss_item_count ?></div>
      </div>
      <div class="kpi-card" style="padding:16px;border-color:#fecaca;background:#fff5f5">
        <div class="kpi-label" style="color:#991b1b">Lowest Margin</div>
        <div class="kpi-value" style="font-size:20px;color:#dc2626"><?= $lowest_margin ?>%</div>
      </div>
    </div>
    <table class="data-table">
      <thead><tr><th>Product</th><th>Category</th><th>Sales</th><th>Profit/Loss</th><th>Margin</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach($loss_products as $lp):
          $lm = $lp['total_sales'] > 0 ? round(($lp['total_profit']/$lp['total_sales'])*100,1) : 0;
          $is_loss = $lp['total_profit'] <= 0;
        ?>
        <tr>
          <td><strong><?= e($lp['product_name']) ?></strong></td>
          <td><span class="badge badge-info"><?= e($lp['category']) ?></span></td>
          <td>₹<?= number_format($lp['total_sales']) ?></td>
          <td style="color:<?= $is_loss ? 'var(--red)' : 'var(--amber)' ?>;font-weight:700">
            <?= $is_loss ? '-' : '' ?>₹<?= number_format(abs($lp['total_profit'])) ?>
          </td>
          <td><span class="badge <?= $is_loss ? 'badge-danger' : 'badge-warning' ?>"><?= $lm ?>%</span></td>
          <td><span class="badge <?= $is_loss ? 'badge-danger' : 'badge-warning' ?>"><?= $is_loss ? '❌ Loss' : '⚠️ Low Margin' ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <!-- Loss Months -->
  <?php if(!empty($loss_months)): ?>
  <div style="margin-bottom:24px">
    <div style="font-weight:700;margin-bottom:16px;font-size:15px;color:var(--red)">📅 Loss Months</div>
    <table class="data-table">
      <thead><tr><th>Month</th><th>Revenue</th><th>Profit/Loss</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach($loss_months as $lm): ?>
        <tr>
          <td><strong><?= e($lm['month']) ?></strong></td>
          <td>₹<?= number_format($lm['revenue']) ?></td>
          <td style="color:var(--red);font-weight:700">-₹<?= number_format(abs($lm['profit'])) ?></td>
          <td><span class="badge badge-danger">❌ Loss Month</span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <!-- Loss Categories -->
  <?php if(!empty($loss_categories)): ?>
  <div>
    <div style="font-weight:700;margin-bottom:16px;font-size:15px;color:var(--amber)">🗂️ Underperforming Categories</div>
    <table class="data-table">
      <thead><tr><th>Category</th><th>Sales</th><th>Profit</th><th>Margin</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach($loss_categories as $lc):
          $lcm = $lc['total_sales'] > 0 ? round(($lc['total_profit']/$lc['total_sales'])*100,1) : 0;
          $lc_loss = $lc['total_profit'] <= 0;
        ?>
        <tr>
          <td><strong><?= e($lc['category']) ?></strong></td>
          <td>₹<?= number_format($lc['total_sales']) ?></td>
          <td style="color:<?= $lc_loss ? 'var(--red)' : 'var(--amber)' ?>;font-weight:700">
            <?= $lc_loss ? '-' : '' ?>₹<?= number_format(abs($lc['total_profit'])) ?>
          </td>
          <td><span class="badge <?= $lc_loss ? 'badge-danger' : 'badge-warning' ?>"><?= $lcm ?>%</span></td>
          <td><span class="badge <?= $lc_loss ? 'badge-danger' : 'badge-warning' ?>"><?= $lc_loss ? '❌ Loss' : '⚠️ Low Margin' ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- UPLOAD HISTORY -->
<?php if(!empty($upload_list)): ?>
<div class="card" style="margin-top:24px">
  <div class="card-header"><div><div class="card-title">Recent Upload History</div></div></div>
  <table class="data-table">
    <thead><tr><th>File Name</th><th>Records Imported</th><th>Uploaded On</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach($upload_list as $u): ?>
      <tr>
        <td>📄 <strong><?= e($u['file_name']) ?></strong></td>
        <td><?= number_format($u['records_imported']) ?> records</td>
        <td><?= date('d M Y, h:i A', strtotime($u['uploaded_at'])) ?></td>
        <td><span class="badge badge-success">✅ Imported</span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<script>
const reportTitles = {
    sales: 'Monthly Sales Report',
    revenue: 'Revenue Report',
    product: 'Product Performance Report',
    profit: 'Profit / Loss Report',
    growth: 'Monthly Growth Report',
    category: 'Category Analytics Report'
};
const reportTitlesEmoji = {
    sales: '📊 Monthly Sales Report',
    revenue: '💰 Revenue Report',
    product: '📦 Product Performance Report',
    profit: '📉 Profit / Loss Report',
    growth: '📅 Monthly Growth Report',
    category: '🗂️ Category Analytics Report'
};

let currentReportType = 'sales';

function showReport(type) {
    currentReportType = type;
    document.getElementById('report-preview-title').textContent = reportTitlesEmoji[type] || 'Report';
    document.getElementById('report-preview').scrollIntoView({behavior:'smooth'});
    showToast('📊 Report preview updated!');
}

// ======== PER-TYPE PDF DATA (PHP -> JS) ========

// Monthly sales data
var monthlyData = [
    <?php foreach($revenue_by_month as $month => $rev):
        $prof = $profit_by_month[$month] ?? 0;
        $qty  = $qty_by_month[$month] ?? 0;
    ?>
    { month: <?= json_encode($month) ?>, revenue: <?= $rev ?>, profit: <?= $prof ?>, qty: <?= $qty ?> },
    <?php endforeach; ?>
];

// Category detail data
var categoryDetailData = [
    <?php foreach($cat_profit_data as $c):
        $margin = $c['total_sales'] > 0 ? round(($c['total_profit']/$c['total_sales'])*100,1) : 0;
    ?>
    { category: <?= json_encode(e($c['category'])) ?>, sales: 'Rs.<?= number_format($c['total_sales']) ?>', profit: 'Rs.<?= number_format($c['total_profit']) ?>', qty: '<?= number_format($c['total_qty']) ?>', margin: '<?= $margin ?>%' },
    <?php endforeach; ?>
];

// Top products data
var topProductsData = [
    <?php foreach($top_products as $i => $p):
        $m = $p['total_sales'] > 0 ? round(($p['total_profit']/$p['total_sales'])*100,1) : 0;
    ?>
    { rank: '#<?= $i+1 ?>', name: <?= json_encode(e($p['product_name'])) ?>, sales: 'Rs.<?= number_format($p['total_sales']) ?>', profit: 'Rs.<?= number_format($p['total_profit']) ?>', margin: '<?= $m ?>%' },
    <?php endforeach; ?>
];

// KPI data
var kpiBase = [
    { label: 'Total Revenue', value: 'Rs.<?= number_format($total_revenue) ?>' },
    { label: 'Total Profit',  value: 'Rs.<?= number_format($total_profit) ?>' },
    { label: 'Units Sold',    value: '<?= number_format($total_orders) ?>' },
    { label: 'Profit Margin', value: '<?= $total_revenue > 0 ? round(($total_profit/$total_revenue)*100,1) : 0 ?>%' }
];

// Loss products data
var lossProductsData = [
    <?php foreach($loss_products as $lp):
        $lm = $lp['total_sales'] > 0 ? round(($lp['total_profit']/$lp['total_sales'])*100,1) : 0;
    ?>
    { name: <?= json_encode(e($lp['product_name'])) ?>, category: <?= json_encode(e($lp['category'])) ?>, sales: 'Rs.<?= number_format($lp['total_sales']) ?>', profit: '<?= $lp['total_profit'] <= 0 ? '-' : '' ?>Rs.<?= number_format(abs($lp['total_profit'])) ?>', margin: '<?= $lm ?>%', status: '<?= $lp['total_profit'] <= 0 ? 'LOSS' : 'LOW MARGIN' ?>' },
    <?php endforeach; ?>
];

// Loss categories data
var lossCategoriesData = [
    <?php foreach($loss_categories as $lc):
        $lcm = $lc['total_sales'] > 0 ? round(($lc['total_profit']/$lc['total_sales'])*100,1) : 0;
    ?>
    { category: <?= json_encode(e($lc['category'])) ?>, sales: 'Rs.<?= number_format($lc['total_sales']) ?>', profit: '<?= $lc['total_profit'] <= 0 ? '-' : '' ?>Rs.<?= number_format(abs($lc['total_profit'])) ?>', margin: '<?= $lcm ?>%', status: '<?= $lc['total_profit'] <= 0 ? 'LOSS' : 'LOW MARGIN' ?>' },
    <?php endforeach; ?>
];

// P&L KPI data
var plKpi = [
    { label: 'Total Revenue', value: 'Rs.<?= number_format($total_revenue) ?>' },
    { label: 'Total Profit',  value: 'Rs.<?= number_format($total_profit) ?>' },
    { label: 'Total Loss',    value: 'Rs.<?= number_format($total_loss) ?>' },
    { label: 'Loss Items',    value: '<?= $loss_item_count ?>' }
];

// ======== GENERATE TYPE-SPECIFIC PDF ========
function generatePDF(type) {
    type = type || currentReportType;

    try {
        var result = createPDFDoc(reportTitles[type] || 'Business Report');
        if (!result) return;
        var doc = result.doc;
        var y = result.y;

        // ---- Add KPI cards (always) ----
        y = addKPICards(doc, y, kpiBase);

        // ---- TYPE-SPECIFIC CONTENT ----
        if (type === 'sales') {
            // Monthly Sales Breakdown
            var salesHead = ['Month', 'Revenue (Rs.)', 'Profit (Rs.)', 'Units Sold'];
            var salesBody = [];
            monthlyData.forEach(function(m) {
                if (m.revenue > 0 || m.profit > 0 || m.qty > 0) {
                    salesBody.push([m.month, 'Rs.' + m.revenue.toLocaleString('en-IN'), 'Rs.' + m.profit.toLocaleString('en-IN'), m.qty.toLocaleString('en-IN')]);
                }
            });
            if (salesBody.length) {
                y = addPDFTable(doc, y, 'Monthly Sales Breakdown', salesHead, salesBody);
            }
        }

        if (type === 'revenue') {
            // Revenue by Month
            var revHead = ['Month', 'Revenue (Rs.)'];
            var revBody = [];
            monthlyData.forEach(function(m) {
                if (m.revenue > 0) {
                    revBody.push([m.month, 'Rs.' + m.revenue.toLocaleString('en-IN')]);
                }
            });
            if (revBody.length) y = addPDFTable(doc, y, 'Revenue by Month', revHead, revBody);

            // Category Revenue
            var catHead = ['Category', 'Revenue', 'Profit', 'Margin'];
            var catBody = categoryDetailData.map(function(c) {
                return [c.category, c.sales, c.profit, c.margin];
            });
            if (catBody.length) y = addPDFTable(doc, y, 'Revenue by Category', catHead, catBody);
        }

        if (type === 'product') {
            // Top Products
            var prodHead = ['Rank', 'Product', 'Total Sales', 'Total Profit', 'Margin'];
            var prodBody = topProductsData.map(function(p) {
                return [p.rank, p.name, p.sales, p.profit, p.margin];
            });
            if (prodBody.length) y = addPDFTable(doc, y, 'Product Performance Ranking', prodHead, prodBody);

            // Category Performance
            var catPHead = ['Category', 'Sales', 'Profit', 'Units', 'Margin'];
            var catPBody = categoryDetailData.map(function(c) {
                return [c.category, c.sales, c.profit, c.qty, c.margin];
            });
            if (catPBody.length) y = addPDFTable(doc, y, 'Category-wise Performance', catPHead, catPBody);
        }

        if (type === 'profit') {
            // Use P&L KPIs instead of base KPIs — re-render at top
            // (KPIs already added above with kpiBase, now add P&L specific ones below)

            // Monthly P&L
            var plHead = ['Month', 'Revenue (Rs.)', 'Profit (Rs.)', 'Margin (%)', 'Status'];
            var plBody = [];
            monthlyData.forEach(function(m) {
                if (m.revenue > 0 || m.profit !== 0) {
                    var mg = m.revenue > 0 ? ((m.profit / m.revenue) * 100).toFixed(1) : '0.0';
                    var status = m.profit <= 0 ? 'LOSS' : (parseFloat(mg) < 10 ? 'LOW' : 'HEALTHY');
                    plBody.push([m.month, 'Rs.' + m.revenue.toLocaleString('en-IN'), (m.profit < 0 ? '-' : '') + 'Rs.' + Math.abs(m.profit).toLocaleString('en-IN'), mg + '%', status]);
                }
            });
            if (plBody.length) y = addPDFTable(doc, y, 'Monthly Profit & Loss', plHead, plBody);

            // Loss-Making Products
            if (lossProductsData.length) {
                var lpHead = ['Product', 'Category', 'Sales', 'Profit/Loss', 'Margin', 'Status'];
                var lpBody = lossProductsData.map(function(p) {
                    return [p.name, p.category, p.sales, p.profit, p.margin, p.status];
                });
                y = addPDFTable(doc, y, 'Loss / Low-Margin Products', lpHead, lpBody);
            }

            // Underperforming Categories
            if (lossCategoriesData.length) {
                var lcHead = ['Category', 'Sales', 'Profit/Loss', 'Margin', 'Status'];
                var lcBody = lossCategoriesData.map(function(c) {
                    return [c.category, c.sales, c.profit, c.margin, c.status];
                });
                y = addPDFTable(doc, y, 'Underperforming Categories', lcHead, lcBody);
            }

            // Category P&L (all categories)
            var cplHead = ['Category', 'Sales', 'Profit', 'Margin'];
            var cplBody = categoryDetailData.map(function(c) {
                return [c.category, c.sales, c.profit, c.margin];
            });
            if (cplBody.length) y = addPDFTable(doc, y, 'Full Category Profit & Loss', cplHead, cplBody);
        }

        if (type === 'growth') {
            // Monthly Growth
            var gHead = ['Month', 'Revenue (Rs.)', 'Growth (%)'];
            var gBody = [];
            var prevRev = 0;
            monthlyData.forEach(function(m) {
                if (m.revenue > 0 || prevRev > 0) {
                    var growth = prevRev > 0 ? (((m.revenue - prevRev) / prevRev) * 100).toFixed(1) : '-';
                    if (growth !== '-') growth = (growth > 0 ? '+' : '') + growth + '%';
                    gBody.push([m.month, 'Rs.' + m.revenue.toLocaleString('en-IN'), growth]);
                }
                if (m.revenue > 0) prevRev = m.revenue;
            });
            if (gBody.length) y = addPDFTable(doc, y, 'Month-over-Month Growth', gHead, gBody);
        }

        if (type === 'category') {
            // Full category analysis
            var fcHead = ['Category', 'Total Sales', 'Total Profit', 'Units Sold', 'Margin'];
            var fcBody = categoryDetailData.map(function(c) {
                return [c.category, c.sales, c.profit, c.qty, c.margin];
            });
            if (fcBody.length) y = addPDFTable(doc, y, 'Category Analytics', fcHead, fcBody);

            // Top products per category breakdown
            var tpHead = ['Rank', 'Product', 'Sales', 'Profit', 'Margin'];
            var tpBody = topProductsData.map(function(p) {
                return [p.rank, p.name, p.sales, p.profit, p.margin];
            });
            if (tpBody.length) y = addPDFTable(doc, y, 'Top Products', tpHead, tpBody);
        }

        savePDF(doc, reportTitles[type] || 'Report');

    } catch(err) {
        console.error('PDF Generation Error:', err);
        alert('Error generating PDF: ' + err.message);
    }
}
</script>
<?php include 'includes/footer.php'; ?>
