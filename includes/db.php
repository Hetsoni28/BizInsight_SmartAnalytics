<?php
// Database Configuration
// Update these settings to match your XAMPP/MySQL setup
define('DB_HOST',     '127.0.0.1');
define('DB_USER',     'root');
define('DB_PASS',     '');
define('DB_NAME',     'bizinsight_db');
define('DB_PORT',     3306);
define('APP_NAME',    'BizInsight');
define('APP_VERSION', '1.0.0');

// Create connection with exception handling
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
} catch (mysqli_sql_exception $e) {
    // Log the real error securely (never expose to user)
    error_log('[BizInsight DB Error] ' . $e->getMessage());
    // Show a user-friendly HTML error page
    http_response_code(503);
    die(<<<HTML
    <!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
    <title>Service Unavailable — BizInsight</title>
    <style>body{font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f8fafc;margin:0;}
    .box{text-align:center;padding:48px;background:#fff;border-radius:16px;border:1.5px solid #e2e8f0;max-width:480px;}
    h2{color:#dc2626;margin-bottom:12px;} p{color:#64748b;line-height:1.6;} code{background:#f1f5f9;padding:2px 8px;border-radius:4px;font-size:13px;}
    </style></head><body><div class="box">
    <h2>⚠️ Database Unavailable</h2>
    <p>Could not connect to the database. Please ensure MySQL is running in XAMPP and the settings in <code>includes/db.php</code> are correct.</p>
    <p><a href="javascript:location.reload()">Try Again</a></p>
    </div></body></html>
    HTML);
}

// Check connection (fallback)
if ($conn->connect_error) {
    error_log('[BizInsight DB Error] ' . $conn->connect_error);
    http_response_code(503);
    die('<h2>Database connection failed. Please check XAMPP MySQL is running.</h2>');
}

// Set charset
$conn->set_charset("utf8mb4");
?>
