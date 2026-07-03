<?php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$user = getCurrentUser();
$uid  = $user['id'];

$success = $error = '';

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if($action === 'profile') {
        $name  = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $city  = trim($_POST['city'] ?? '');
        if($name) {
            $st = $conn->prepare("UPDATE users SET name=?, phone=?, city=? WHERE id=?");
            $st->bind_param("sssi", $name, $phone, $city, $uid);
            $st->execute();
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully!';
            $profile['name'] = $name;
            $profile['phone'] = $phone;
            $profile['city'] = $city;
        } else {
            $error = 'Name is required.';
        }
    }

    if($action === 'business') {
        $biz  = trim($_POST['business_name'] ?? '');
        $type = trim($_POST['business_type'] ?? '');
        $gst  = trim($_POST['gst'] ?? '');
        $curr = trim($_POST['currency'] ?? 'INR');
        if($biz) {
            $st = $conn->prepare("UPDATE users SET business_name=?, business_type=?, gst=?, currency=? WHERE id=?");
            $st->bind_param("ssssi", $biz, $type, $gst, $curr, $uid);
            $st->execute();
            $success = 'Business settings saved!';
        }
    }

    if($action === 'password') {
        $current  = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        if($new_pass !== $confirm) {
            $error = 'New password and confirm password do not match.';
        } elseif(strlen($new_pass) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif(!password_verify($current, $profile['password'])) {
            $error = 'Current password is incorrect.';
        } else {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $st = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $st->bind_param("si", $hashed, $uid);
            $st->execute();
            $success = 'Password changed successfully!';
        }
    }
}

$active_tab = $_GET['tab'] ?? 'profile';
$page_title = 'Settings';
include 'includes/header.php';
?>

<div class="page-header">
  <div>
    <h1><i class='bx bx-cog'></i> Settings</h1>
    <p>Manage your account, business, and dashboard preferences.</p>
  </div>
</div>

<?php if($success): ?><div class="alert alert-success"><i class='bx bx-check-circle'></i> <?= e($success) ?></div><?php endif; ?>
<?php if($error):   ?><div class="alert alert-danger"><i class='bx bx-error-circle'></i> <?= e($error) ?></div><?php endif; ?>

<div class="settings-grid">

  <!-- SETTINGS NAV -->
  <div class="card settings-nav" style="padding:16px;height:fit-content">
    <div class="settings-nav-avatar" style="text-align:center;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--border)">
      <div class="user-avatar" style="margin:0 auto 10px;width:60px;height:60px;font-size:22px"><?= strtoupper(substr($profile['name'],0,2)) ?></div>
      <div style="font-weight:700"><?= e($profile['name']) ?></div>
      <div style="font-size:12px;color:var(--text-muted)"><?= e($profile['email']) ?></div>
    </div>
    <div class="settings-nav-links">
      <a href="?tab=profile" class="settings-link <?= $active_tab==='profile'?'active':'' ?>"><i class='bx bx-user'></i> Profile</a>
      <a href="?tab=business" class="settings-link <?= $active_tab==='business'?'active':'' ?>"><i class='bx bx-buildings'></i> Business</a>
      <a href="?tab=security" class="settings-link <?= $active_tab==='security'?'active':'' ?>"><i class='bx bx-lock-alt'></i> Security</a>
      <a href="?tab=notifications" class="settings-link <?= $active_tab==='notifications'?'active':'' ?>"><i class='bx bx-bell'></i> Notifications</a>
      <a href="?tab=preferences" class="settings-link <?= $active_tab==='preferences'?'active':'' ?>"><i class='bx bx-palette'></i> Preferences</a>
    </div>
  </div>

  <!-- SETTINGS CONTENT -->
  <div>

    <!-- PROFILE TAB -->
    <?php if($active_tab === 'profile'): ?>
    <div class="card">
      <div class="card-title" style="margin-bottom:24px">Profile Settings</div>
      <form method="POST">
        <input type="hidden" name="action" value="profile">
        <div class="form-row-grid">
          <div class="form-group"><label>Full Name <span class="req">*</span></label><input type="text" name="name" class="input-field" value="<?= e($profile['name']) ?>" required></div>
          <div class="form-group"><label>Email (read-only)</label><input type="email" class="input-field" value="<?= e($profile['email']) ?>" readonly style="opacity:.6;cursor:not-allowed"></div>
        </div>
        <div class="form-row-grid">
          <div class="form-group"><label>Phone Number</label><input type="text" name="phone" class="input-field" value="<?= e($profile['phone'] ?? '') ?>" placeholder="+91 98765 43210"></div>
          <div class="form-group"><label>City / Location</label><input type="text" name="city" class="input-field" value="<?= e($profile['city'] ?? '') ?>" placeholder="Mumbai, India"></div>
        </div>
        <button type="submit" class="btn btn-primary">Save Profile</button>
      </form>
    </div>

    <!-- BUSINESS TAB -->
    <?php elseif($active_tab === 'business'): ?>
    <div class="card">
      <div class="card-title" style="margin-bottom:24px">Business Settings</div>
      <form method="POST">
        <input type="hidden" name="action" value="business">
        <div class="form-row-grid">
          <div class="form-group"><label>Business Name</label><input type="text" name="business_name" class="input-field" value="<?= e($profile['business_name'] ?? '') ?>" placeholder="Your Company Name"></div>
          <div class="form-group"><label>Business Type</label>
            <select name="business_type" class="input-field">
              <?php foreach(['Retail Store','E-commerce','Service Business','Manufacturing','Wholesale','Startup'] as $t): ?>
                <option <?= ($profile['business_type']??'')===$t?'selected':'' ?>><?= $t ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row-grid">
          <div class="form-group"><label>GST Number</label><input type="text" name="gst" class="input-field" value="<?= e($profile['gst'] ?? '') ?>" placeholder="27ABCDE1234F1Z5"></div>
          <div class="form-group"><label>Currency</label>
            <select name="currency" class="input-field">
              <option value="INR" <?= ($profile['currency']??'')==='INR'?'selected':'' ?>>INR (₹)</option>
              <option value="USD" <?= ($profile['currency']??'')==='USD'?'selected':'' ?>>USD ($)</option>
              <option value="EUR" <?= ($profile['currency']??'')==='EUR'?'selected':'' ?>>EUR (€)</option>
            </select>
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Business Settings</button>
      </form>
    </div>

    <!-- SECURITY TAB -->
    <?php elseif($active_tab === 'security'): ?>
    <div class="card">
      <div class="card-title" style="margin-bottom:24px">Change Password</div>
      <form method="POST">
        <input type="hidden" name="action" value="password">
        <div class="form-group"><label>Current Password</label><input type="password" name="current_password" class="input-field" placeholder="Enter current password" required></div>
        <div class="form-row-grid">
          <div class="form-group"><label>New Password</label><input type="password" name="new_password" class="input-field" placeholder="Min 8 characters" required minlength="8"></div>
          <div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" class="input-field" placeholder="Repeat new password" required></div>
        </div>
        <button type="submit" class="btn btn-primary">Update Password</button>
      </form>
    </div>

    <!-- NOTIFICATIONS TAB -->
    <?php elseif($active_tab === 'notifications'): ?>
    <div class="card">
      <div class="card-title" style="margin-bottom:24px">Notification Preferences</div>
      <div style="display:flex;flex-direction:column;gap:0">
        <?php
        $notifs = [
          ['Monthly Report Ready', 'Get notified when your monthly report is generated'],
          ['Revenue Alert', 'Alert when revenue drops more than 10%'],
          ['Upload Success', 'Confirm when data upload is complete'],
          ['Low Profit Warning', 'Alert when profit margin falls below 10%'],
          ['Weekly Summary', 'Receive a weekly business summary email'],
        ];
        foreach($notifs as $n): ?>
        <div class="notif-row">
          <div><div style="font-weight:600;margin-bottom:4px"><?= $n[0] ?></div><div style="font-size:13px;color:var(--text-muted)"><?= $n[1] ?></div></div>
          <label style="cursor:pointer;display:flex;align-items:center;gap:8px;font-size:13px;white-space:nowrap"><input type="checkbox" checked> Enabled</label>
        </div>
        <?php endforeach; ?>
      </div>
      <button class="btn btn-primary" style="margin-top:20px" onclick="showToast('<i class="bx bx-bell"></i> Notification preferences saved!')">Save Preferences</button>
    </div>

    <!-- PREFERENCES TAB -->
    <?php elseif($active_tab === 'preferences'): ?>
    <div class="card">
      <div class="card-title" style="margin-bottom:24px">Dashboard Preferences</div>
      <div class="form-row-grid">
        <div class="form-group"><label>Default Date Range</label><select class="input-field"><option>This Month</option><option>Last 3 Months</option><option>This Year</option><option>All Time</option></select></div>
        <div class="form-group"><label>Default Chart Type</label><select class="input-field"><option>Bar Chart</option><option>Line Chart</option><option>Area Chart</option><option>Pie Chart</option></select></div>
      </div>
      <div class="form-row-grid">
        <div class="form-group"><label>Records Per Page</label><select class="input-field"><option>10</option><option>25</option><option>50</option><option>100</option></select></div>
        <div class="form-group"><label>Date Format</label><select class="input-field"><option>DD/MM/YYYY</option><option>MM/DD/YYYY</option><option>YYYY-MM-DD</option></select></div>
      </div>
      <button class="btn btn-primary" onclick="showToast('<i class="bx bx-cog"></i> Preferences saved!')">Save Preferences</button>
    </div>
    <?php endif; ?>

  </div>
</div>

<style>
/* Settings page layout */
.settings-grid {
  display: grid;
  grid-template-columns: 220px 1fr;
  gap: 24px;
}

.notif-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0;
  border-bottom: 1px solid var(--border);
  gap: 16px;
}

/* Tablet: sidebar becomes horizontal nav */
@media (max-width: 900px) {
  .settings-grid {
    grid-template-columns: 1fr;
  }
  .settings-nav {
    position: sticky;
    top: 60px;
    z-index: 50;
  }
  .settings-nav-avatar {
    display: none !important;
  }
  .settings-nav-links {
    display: flex;
    gap: 4px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 4px;
  }
  .settings-nav-links .settings-link {
    white-space: nowrap;
    flex-shrink: 0;
    margin-bottom: 0;
  }
}

/* Mobile: stack notification rows */
@media (max-width: 600px) {
  .notif-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
}
</style>

<?php include 'includes/footer.php'; ?>

