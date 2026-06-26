<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$user = getCurrentUser();
$uid  = $user['id'];

$success = $error = '';

// DELETE
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM sales WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $did, $uid);
    $stmt->execute();
    $success = 'Sale record deleted successfully.';
}

// ADD NEW SALE
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $product  = trim($_POST['product_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $sales    = (float)($_POST['sales_amount'] ?? 0);
    $profit   = (float)($_POST['profit'] ?? 0);
    $qty      = (int)($_POST['quantity'] ?? 0);
    $month    = trim($_POST['month_name'] ?? '');
    if($product && $category && $sales > 0 && $month) {
        // i=user_id, s=product, s=category, d=sales, d=profit, i=qty, s=month
        $stmt = $conn->prepare("INSERT INTO sales (user_id, product_name, category, sales_amount, profit, quantity, month_name) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("issddis", $uid, $product, $category, $sales, $profit, $qty, $month);
        $stmt->execute();
        $success = 'Sale record added successfully!';
    } else {
        $error = 'Please fill all required fields.';
    }
}

// EDIT
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $eid     = (int)$_POST['edit_id'];
    $product  = trim($_POST['product_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $sales    = (float)($_POST['sales_amount'] ?? 0);
    $profit   = (float)($_POST['profit'] ?? 0);
    $qty      = (int)($_POST['quantity'] ?? 0);
    $month    = trim($_POST['month_name'] ?? '');
    $stmt = $conn->prepare("UPDATE sales SET product_name=?, category=?, sales_amount=?, profit=?, quantity=?, month_name=? WHERE id=? AND user_id=?");
    $stmt->bind_param("ssddisii", $product, $category, $sales, $profit, $qty, $month, $eid, $uid);
    $stmt->execute();
    $success = 'Sale record updated successfully!';
}

// Filters
$search   = trim($_GET['search'] ?? '');
$cat_f    = trim($_GET['category'] ?? '');
$month_f  = trim($_GET['month'] ?? '');

// Build query
$where = "WHERE user_id = ?";
$params = [$uid];
$types  = "i";
if($search) {
    $like = "%$search%";
    $where .= " AND product_name LIKE ?";
    $params[] = $like;
    $types   .= "s";
}
if($cat_f) {
    $where .= " AND category = ?";
    $params[] = $cat_f;
    $types   .= "s";
}
if($month_f) {
    $where .= " AND month_name = ?";
    $params[] = $month_f;
    $types   .= "s";
}

// Pagination
$per_page = 10;
$page_num = max(1, (int)($_GET['p'] ?? 1));
$offset   = ($page_num - 1) * $per_page;

$count_stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM sales $where");
$count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total_rows  = $count_stmt->get_result()->fetch_assoc()['cnt'];
$total_pages = ceil($total_rows / $per_page);

$params_paged = $params;
$types_paged  = $types . "ii";
$params_paged[] = $per_page;
$params_paged[] = $offset;

$stmt = $conn->prepare("SELECT * FROM sales $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param($types_paged, ...$params_paged);
$stmt->execute();
$sales_records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get categories for filter
$cat_res = $conn->prepare("SELECT DISTINCT category FROM sales WHERE user_id = ? ORDER BY category");
$cat_res->bind_param("i", $uid);
$cat_res->execute();
$categories = $cat_res->get_result()->fetch_all(MYSQLI_ASSOC);

$months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

$page_title = 'Sales Data';
include 'includes/header.php';
?>

<div class="page-header">
  <div>
    <h1>🛒 Sales Data Management</h1>
    <p>Add, edit, search, and manage all your sales records.</p>
  </div>
  <button class="btn btn-primary" onclick="openModal('addModal')">+ Add New Sale</button>
</div>

<?php if($success): ?><div class="alert alert-success">✅ <?= e($success) ?></div><?php endif; ?>
<?php if($error):   ?><div class="alert alert-danger">⚠️ <?= e($error) ?></div><?php endif; ?>

<!-- FILTER BAR -->
<form method="GET" class="filter-bar">
  <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
    <input type="text" name="search" class="input-field" style="width:220px" placeholder="🔍 Search products..." value="<?= e($search) ?>">
    <select name="category" class="input-field" style="width:160px">
      <option value="">All Categories</option>
      <?php foreach($categories as $c): ?>
        <option value="<?= e($c['category']) ?>" <?= $cat_f === $c['category'] ? 'selected' : '' ?>><?= e($c['category']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="month" class="input-field" style="width:160px">
      <option value="">All Months</option>
      <?php foreach($months as $m): ?>
        <option value="<?= $m ?>" <?= $month_f === $m ? 'selected' : '' ?>><?= $m ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Search</button>
    <a href="sales.php" class="btn btn-outline">Clear</a>
    <a href="reports.php?type=sales_csv" class="btn btn-outline">Export CSV</a>
    <button class="btn btn-outline" onclick="generatePagePDF('Sales Data Report')">📄 PDF</button>
  </div>
</form>

<div class="card">
  <div class="card-header">
    <div><div class="card-title">Sales Records</div><div class="card-sub"><?= $total_rows ?> records found</div></div>
  </div>
  <?php if(empty($sales_records)): ?>
    <div class="empty-state">
      <div style="font-size:48px;margin-bottom:12px">🛒</div>
      <p>No sales records found. <a href="#" onclick="openModal('addModal')">Add your first sale</a> or <a href="upload.php">upload a CSV file</a>.</p>
    </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>ID</th><th>Product Name</th><th>Category</th><th>Qty</th><th>Sales Amount</th><th>Profit</th><th>Month</th><th>Date</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach($sales_records as $s): ?>
        <tr>
          <td>#<?= $s['id'] ?></td>
          <td><strong><?= e($s['product_name']) ?></strong></td>
          <td><span class="badge badge-info"><?= e($s['category']) ?></span></td>
          <td><?= number_format($s['quantity']) ?></td>
          <td>₹<?= number_format($s['sales_amount']) ?></td>
          <td>₹<?= number_format($s['profit']) ?></td>
          <td><?= e($s['month_name']) ?></td>
          <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
          <td>
            <button class="btn btn-outline btn-sm"
              onclick="openEditModal(<?= $s['id'] ?>, '<?= e($s['product_name']) ?>', '<?= e($s['category']) ?>', <?= $s['sales_amount'] ?>, <?= $s['profit'] ?>, <?= $s['quantity'] ?>, '<?= e($s['month_name']) ?>')">
              Edit
            </button>
            <a href="sales.php?delete=<?= $s['id'] ?>" class="btn btn-sm btn-danger"
               onclick="return confirm('Delete this record?')">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- PAGINATION -->
  <?php if($total_pages > 1): ?>
  <div class="pagination">
    <span><?= $total_rows ?> total records</span>
    <div style="display:flex;gap:8px">
      <?php if($page_num > 1): ?>
        <a href="?p=<?= $page_num - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($cat_f) ?>&month=<?= urlencode($month_f) ?>" class="btn btn-outline btn-sm">← Prev</a>
      <?php endif; ?>
      <?php for($i = max(1,$page_num-2); $i <= min($total_pages,$page_num+2); $i++): ?>
        <a href="?p=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($cat_f) ?>&month=<?= urlencode($month_f) ?>"
           class="btn btn-sm <?= $i === $page_num ? 'btn-primary' : 'btn-outline' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <?php if($page_num < $total_pages): ?>
        <a href="?p=<?= $page_num + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($cat_f) ?>&month=<?= urlencode($month_f) ?>" class="btn btn-outline btn-sm">Next →</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    <h3>➕ Add New Sale Record</h3>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="form-row-grid">
        <div class="form-group"><label>Product Name <span class="req">*</span></label><input type="text" name="product_name" class="input-field" placeholder="e.g. Laptop Pro X1" required></div>
        <div class="form-group"><label>Category <span class="req">*</span></label>
          <select name="category" class="input-field" required>
            <option value="">Select category</option>
            <option>Electronics</option><option>Phones</option><option>Tablets</option><option>Audio</option><option>Accessories</option><option>Clothing</option><option>Food & Beverage</option><option>Furniture</option><option>Other</option>
          </select>
        </div>
      </div>
      <div class="form-row-grid">
        <div class="form-group"><label>Sales Amount (₹) <span class="req">*</span></label><input type="number" name="sales_amount" class="input-field" placeholder="50000" min="0" step="0.01" required></div>
        <div class="form-group"><label>Profit (₹)</label><input type="number" name="profit" class="input-field" placeholder="8000" min="0" step="0.01"></div>
      </div>
      <div class="form-row-grid">
        <div class="form-group"><label>Quantity Sold</label><input type="number" name="quantity" class="input-field" placeholder="5" min="0"></div>
        <div class="form-group"><label>Month <span class="req">*</span></label>
          <select name="month_name" class="input-field" required>
            <option value="">Select month</option>
            <?php foreach($months as $m): ?><option><?= $m ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div style="display:flex;gap:12px;margin-top:8px">
        <button type="submit" class="btn btn-primary">Save Record</button>
        <button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    <h3>✏️ Edit Sale Record</h3>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="edit_id" id="edit_id">
      <div class="form-row-grid">
        <div class="form-group"><label>Product Name</label><input type="text" name="product_name" id="edit_product" class="input-field" required></div>
        <div class="form-group"><label>Category</label>
          <select name="category" id="edit_category" class="input-field">
            <option>Electronics</option><option>Phones</option><option>Tablets</option><option>Audio</option><option>Accessories</option><option>Clothing</option><option>Food & Beverage</option><option>Furniture</option><option>Other</option>
          </select>
        </div>
      </div>
      <div class="form-row-grid">
        <div class="form-group"><label>Sales Amount (₹)</label><input type="number" name="sales_amount" id="edit_sales" class="input-field"></div>
        <div class="form-group"><label>Profit (₹)</label><input type="number" name="profit" id="edit_profit" class="input-field"></div>
      </div>
      <div class="form-row-grid">
        <div class="form-group"><label>Quantity</label><input type="number" name="quantity" id="edit_qty" class="input-field"></div>
        <div class="form-group"><label>Month</label>
          <select name="month_name" id="edit_month" class="input-field">
            <?php foreach($months as $m): ?><option><?= $m ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div style="display:flex;gap:12px;margin-top:8px">
        <button type="submit" class="btn btn-primary">Update Record</button>
        <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditModal(id, product, category, sales, profit, qty, month) {
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_product').value = product;
  document.getElementById('edit_category').value = category;
  document.getElementById('edit_sales').value = sales;
  document.getElementById('edit_profit').value = profit;
  document.getElementById('edit_qty').value = qty;
  document.getElementById('edit_month').value = month;
  openModal('editModal');
}
</script>
<?php include 'includes/footer.php'; ?>
