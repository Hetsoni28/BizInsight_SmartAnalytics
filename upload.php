<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$user = getCurrentUser();
$uid  = $user['id'];

$success = $error = '';
$imported_count = 0;

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvfile'])) {
    $file = $_FILES['csvfile'];
    $allowed_types = ['text/csv','application/vnd.ms-excel','text/plain','application/csv'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'File upload failed. Please try again.';
    } elseif(!in_array($file_ext, ['csv', 'txt'])) {
        $error = 'Only CSV files are allowed. Please upload a .csv file.';
    } elseif($file['size'] > 5 * 1024 * 1024) {
        $error = 'File size must be under 5MB.';
    } else {
        // Save file
        $upload_dir = 'uploads/files/';
        if(!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $saved_name = uniqid('upload_') . '_' . time() . '.csv';
        $saved_path = $upload_dir . $saved_name;

        if(move_uploaded_file($file['tmp_name'], $saved_path)) {
            // Parse CSV
            $handle = fopen($saved_path, 'r');
            $header = fgetcsv($handle); // Skip header row
            $valid_months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

            if($header === false) {
                $error = 'Could not read the CSV file. Please check the format.';
            } else {
                $stmt = $conn->prepare("INSERT INTO sales (user_id, product_name, category, sales_amount, profit, quantity, month_name) VALUES (?,?,?,?,?,?,?)");
                while(($row = fgetcsv($handle)) !== false) {
                    if(count($row) < 6) continue;
                    $product  = trim($row[0] ?? '');
                    $category = trim($row[1] ?? 'Other');
                    $sales    = (float)($row[2] ?? 0);
                    $profit   = (float)($row[3] ?? 0);
                    $qty      = (int)($row[4] ?? 0);
                    $month    = trim($row[5] ?? '');
                    if(!$product || $sales <= 0) continue;
                    if(!in_array($month, $valid_months)) $month = 'January';
                    $stmt->bind_param("issddis", $uid, $product, $category, $sales, $profit, $qty, $month);
                    if($stmt->execute()) $imported_count++;
                }
                fclose($handle);

                // Log upload
                $log = $conn->prepare("INSERT INTO uploads (user_id, file_name, records_imported) VALUES (?,?,?)");
                $log->bind_param("isi", $uid, $file['name'], $imported_count);
                $log->execute();

                $success = "<i class='bx bx-check-circle'></i> File uploaded successfully! $imported_count records imported into your dashboard.";
            }
        } else {
            $error = 'Failed to save the uploaded file. Please check folder permissions.';
        }
    }
}

// Get recent uploads
$upstmt = $conn->prepare("SELECT * FROM uploads WHERE user_id=? ORDER BY uploaded_at DESC LIMIT 8");
$upstmt->bind_param("i", $uid);
$upstmt->execute();
$uploads = $upstmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = 'Upload Data';
include 'includes/header.php';
?>

<div class="page-header">
  <div>
    <h1><i class='bx bx-folder-open'></i> Upload Sales Data</h1>
    <p>Upload your CSV or Excel files to automatically populate your analytics dashboard.</p>
  </div>
</div>

<?php if($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
<?php if($error):   ?><div class="alert alert-danger"><i class='bx bx-error-circle'></i> <?= e($error) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:28px">

  <!-- UPLOAD FORM -->
  <div class="card">
    <div class="card-title" style="margin-bottom:20px">Upload CSV File</div>
    <form method="POST" enctype="multipart/form-data" id="uploadForm">
      <div class="upload-zone" id="dropZone" onclick="document.getElementById('csvfile').click()">
        <div class="upload-icon"><i class='bx bx-folder'></i></div>
        <h3>Drop your CSV file here</h3>
        <p>or click to browse your computer</p>
        <p style="margin-top:8px;font-size:12px;color:#94a3b8">Supported: .csv files up to 5MB</p>
        <div id="fileName" style="margin-top:12px;font-weight:600;color:var(--blue);display:none"></div>
      </div>
      <input type="file" name="csvfile" id="csvfile" accept=".csv,.txt" style="display:none" onchange="handleFileSelect(this)">
      <div style="margin-top:16px;display:flex;gap:12px">
        <button type="submit" class="btn btn-primary" id="uploadBtn" disabled><i class='bx bx-upload'></i> Upload & Import</button>
        <a href="uploads/sample_data.csv" download class="btn btn-outline"><i class='bx bx-download'></i> Download Sample CSV</a>
      </div>
    </form>
  </div>

  <!-- FORMAT GUIDE -->
  <div class="card">
    <div class="card-title" style="margin-bottom:20px"><i class='bx bx-clipboard'></i> Required CSV Format</div>
    <div class="code-block">
      product_name, category, sales_amount, profit, quantity, month_name<br>
      Laptop Pro X1, Electronics, 50000, 8000, 5, January<br>
      Mobile Galaxy S, Phones, 30000, 5000, 8, February<br>
      Smart TV 55", Electronics, 45000, 7200, 3, March<br>
      Headphones Pro, Audio, 12000, 3500, 12, April
    </div>
    <div style="margin-top:16px">
      <div style="font-weight:700;margin-bottom:12px;font-size:14px">Column Description:</div>
      <table class="data-table" style="font-size:13px">
        <thead><tr><th>Column</th><th>Type</th><th>Required</th><th>Example</th></tr></thead>
        <tbody>
          <tr><td>product_name</td><td>Text</td><td><span class="badge badge-danger">Yes</span></td><td>Laptop Pro X1</td></tr>
          <tr><td>category</td><td>Text</td><td><span class="badge badge-warning">Optional</span></td><td>Electronics</td></tr>
          <tr><td>sales_amount</td><td>Number</td><td><span class="badge badge-danger">Yes</span></td><td>50000</td></tr>
          <tr><td>profit</td><td>Number</td><td><span class="badge badge-warning">Optional</span></td><td>8000</td></tr>
          <tr><td>quantity</td><td>Number</td><td><span class="badge badge-warning">Optional</span></td><td>5</td></tr>
          <tr><td>month_name</td><td>Text</td><td><span class="badge badge-danger">Yes</span></td><td>January</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- WHAT HAPPENS NEXT -->
<div class="card" style="margin-bottom:28px">
  <div class="card-title" style="margin-bottom:20px"><i class='bx bx-check-circle'></i> What Happens After Upload</div>
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px">
    <div style="text-align:center;padding:20px">
      <div style="width:48px;height:48px;background:var(--blue);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;margin:0 auto 12px">1</div>
      <div style="font-weight:700;margin-bottom:6px">File Validation</div>
      <div style="font-size:13px;color:var(--text-muted)">Format and column structure is checked automatically</div>
    </div>
    <div style="text-align:center;padding:20px">
      <div style="width:48px;height:48px;background:var(--green);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;margin:0 auto 12px">2</div>
      <div style="font-weight:700;margin-bottom:6px">Data Parsed</div>
      <div style="font-size:13px;color:var(--text-muted)">Each row is read and validated for required fields</div>
    </div>
    <div style="text-align:center;padding:20px">
      <div style="width:48px;height:48px;background:var(--amber);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;margin:0 auto 12px">3</div>
      <div style="font-weight:700;margin-bottom:6px">Saved to Database</div>
      <div style="font-size:13px;color:var(--text-muted)">Records stored in MySQL sales table</div>
    </div>
    <div style="text-align:center;padding:20px">
      <div style="width:48px;height:48px;background:var(--purple);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;margin:0 auto 12px">4</div>
      <div style="font-weight:700;margin-bottom:6px">Dashboard Updated</div>
      <div style="font-size:13px;color:var(--text-muted)">Charts, KPIs and reports refresh automatically</div>
    </div>
  </div>
</div>

<!-- RECENT UPLOADS -->
<div class="card">
  <div class="card-header"><div><div class="card-title">Recent Upload History</div></div></div>
  <?php if(empty($uploads)): ?>
    <div class="empty-state"><div style="font-size:48px;margin-bottom:12px"><i class='bx bx-folder-open'></i></div><p>No uploads yet. Upload your first CSV file above.</p></div>
  <?php else: ?>
  <table class="data-table">
    <thead><tr><th>File Name</th><th>Records Imported</th><th>Uploaded On</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach($uploads as $u): ?>
      <tr>
        <td><i class='bx bx-file-blank'></i> <strong><?= e($u['file_name']) ?></strong></td>
        <td><?= number_format($u['records_imported']) ?> records</td>
        <td><?= date('d M Y, h:i A', strtotime($u['uploaded_at'])) ?></td>
        <td><span class="badge badge-success"><i class='bx bx-check-circle'></i> Imported</span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<script>
function handleFileSelect(input) {
    const file = input.files[0];
    if(file) {
        document.getElementById('fileName').textContent = '<i class="bx bx-file-blank"></i> ' + file.name;
        document.getElementById('fileName').style.display = 'block';
        document.getElementById('uploadBtn').disabled = false;
        document.querySelector('.upload-zone h3').textContent = 'File selected!';
    }
}
// Drag & drop
const dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor='var(--blue)'; });
dropZone.addEventListener('dragleave', () => dropZone.style.borderColor='');
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.style.borderColor = '';
    const dt = e.dataTransfer;
    if(dt.files.length) {
        document.getElementById('csvfile').files = dt.files;
        handleFileSelect(document.getElementById('csvfile'));
    }
});
</script>
<?php include 'includes/footer.php'; ?>
