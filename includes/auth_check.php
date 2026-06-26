<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Auto-repair session if user_name or user_email are missing
// (happens when session was created by an older version of login.php)
if (!isset($_SESSION['user_name']) || !isset($_SESSION['user_email'])) {
    require_once __DIR__ . '/db.php';
    $repair = $conn->prepare("SELECT name, email FROM users WHERE id = ? LIMIT 1");
    $repair->bind_param("i", $_SESSION['user_id']);
    $repair->execute();
    $repair_row = $repair->get_result()->fetch_assoc();
    if ($repair_row) {
        $_SESSION['user_name']  = $repair_row['name'];
        $_SESSION['user_email'] = $repair_row['email'];
    } else {
        // User no longer exists — destroy session and redirect
        session_destroy();
        header("Location: login.php");
        exit();
    }
}

// Helper function to get current user (always safe after repair above)
function getCurrentUser() {
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name']  ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
    ];
}
?>
