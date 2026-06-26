<?php
// ============================================================
//  BizInsight — Helper Functions
// ============================================================

/**
 * Format number as Indian currency string
 */
function formatCurrency($amount) {
    if($amount >= 100000) return '₹' . number_format($amount / 100000, 2) . 'L';
    if($amount >= 1000)   return '₹' . number_format($amount / 1000, 1) . 'K';
    return '₹' . number_format($amount, 0);
}

/**
 * Calculate growth percentage between two values
 */
function growthPercent($current, $previous) {
    if($previous == 0) return 0;
    return round((($current - $previous) / $previous) * 100, 1);
}

/**
 * Get total revenue for a user
 */
function getTotalRevenue($conn, $user_id, $month = null) {
    $sql = "SELECT COALESCE(SUM(sales_amount), 0) as total FROM sales WHERE user_id = ?";
    $params = [$user_id];
    $types  = "i";
    if($month) {
        $sql .= " AND month_name = ?";
        $params[] = $month;
        $types   .= "s";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'];
}

/**
 * Get total profit for a user
 */
function getTotalProfit($conn, $user_id, $month = null) {
    $sql = "SELECT COALESCE(SUM(profit), 0) as total FROM sales WHERE user_id = ?";
    $params = [$user_id];
    $types  = "i";
    if($month) {
        $sql .= " AND month_name = ?";
        $params[] = $month;
        $types   .= "s";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'];
}

/**
 * Get total order count for a user
 */
function getTotalOrders($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) as total FROM sales WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'];
}

/**
 * Get revenue by month (for chart) — single query with GROUP BY
 */
function getRevenueByMonth($conn, $user_id) {
    $months = ['January','February','March','April','May','June',
               'July','August','September','October','November','December'];
    // Pre-fill all months with 0 to ensure all 12 bars appear on chart
    $data = array_fill_keys($months, 0.0);

    $stmt = $conn->prepare(
        "SELECT month_name, COALESCE(SUM(sales_amount), 0) as total
         FROM sales WHERE user_id = ?
         GROUP BY month_name"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($rows as $row) {
        if (isset($data[$row['month_name']])) {
            $data[$row['month_name']] = (float)$row['total'];
        }
    }
    return $data;
}

/**
 * Get sales by category (for pie chart)
 */
function getSalesByCategory($conn, $user_id) {
    $stmt = $conn->prepare("SELECT category, SUM(sales_amount) as total FROM sales WHERE user_id = ? GROUP BY category ORDER BY total DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get top products
 */
function getTopProducts($conn, $user_id, $limit = 5) {
    $stmt = $conn->prepare("SELECT product_name, SUM(sales_amount) as total_sales, SUM(profit) as total_profit FROM sales WHERE user_id = ? GROUP BY product_name ORDER BY total_sales DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Sanitize output
 */
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
