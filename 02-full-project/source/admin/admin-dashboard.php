<?php 
require_once "../config.php";

// Admin auth guard
require_once 'admin-auth.php';

$admin_name = $_SESSION['admin_username'] ?? 'Admin';
$admin_email = $_SESSION['admin_email'] ?? '';
$admin_id = (int)$_SESSION['admin_id'];

// ── Ensure notification_read table exists ──
$conn->query("CREATE TABLE IF NOT EXISTS `notification_read` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `notif_key` varchar(100) NOT NULL,
  `read_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_read` (`admin_id`, `notif_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// ── Handle Mark as Read / Mark All as Read ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notif_action'])) {
    if ($_POST['notif_action'] === 'mark_read' && !empty($_POST['notif_key'])) {
        $key = $_POST['notif_key'];
        $stmt = $conn->prepare("INSERT IGNORE INTO notification_read (admin_id, notif_key) VALUES (?, ?)");
        $stmt->bind_param("is", $admin_id, $key);
        $stmt->execute();
        $stmt->close();
    }
    if ($_POST['notif_action'] === 'mark_all_read' && !empty($_POST['notif_keys'])) {
        $keys = json_decode($_POST['notif_keys'], true);
        if (is_array($keys)) {
            $stmt = $conn->prepare("INSERT IGNORE INTO notification_read (admin_id, notif_key) VALUES (?, ?)");
            foreach ($keys as $key) {
                $stmt->bind_param("is", $admin_id, $key);
                $stmt->execute();
            }
            $stmt->close();
        }
    }
    header("Location: admin-dashboard"); exit();
}

// ── Load read notification keys for this admin ──
$read_keys = [];
$r = $conn->query("SELECT notif_key FROM notification_read WHERE admin_id = $admin_id");
if ($r) { while ($row = $r->fetch_assoc()) $read_keys[] = $row['notif_key']; }

// Fetch dynamic stats
$total_revenue = 0;
$total_orders = 0;
$total_products = 0;
$total_customers = 0;

$r = $conn->query("SELECT COALESCE(SUM(total_price),0) as rev, COUNT(*) as cnt FROM `order`");
if ($r) { $row = $r->fetch_assoc(); $total_revenue = $row['rev']; $total_orders = $row['cnt']; }

$r = $conn->query("SELECT COUNT(*) as cnt FROM product");
if ($r) { $total_products = $r->fetch_assoc()['cnt']; }

$r = $conn->query("SELECT COUNT(*) as cnt FROM customer");
if ($r) { $total_customers = $r->fetch_assoc()['cnt']; }

// ── Sales Overview: Daily revenue + order count for last 7 days ──
$daily_sales = [];
$day_labels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $day_name = $day_labels[(int)date('w', strtotime($date))];
    $daily_sales[$date] = ['label' => $day_name, 'revenue' => 0, 'orders' => 0];
}
$r = $conn->query("SELECT DATE(created_at) as day, COALESCE(SUM(total_price),0) as rev, COUNT(*) as cnt 
    FROM `order` WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
    GROUP BY DATE(created_at) ORDER BY day ASC");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        if (isset($daily_sales[$row['day']])) {
            $daily_sales[$row['day']]['revenue'] = (float)$row['rev'];
            $daily_sales[$row['day']]['orders'] = (int)$row['cnt'];
        }
    }
}
// Calculate max values for bar height percentages
$max_revenue = max(1, max(array_column($daily_sales, 'revenue')));
$max_orders = max(1, max(array_column($daily_sales, 'orders')));

// ── Top Categories: Sales percentage from real order data ──
$category_sales = [];
$total_category_revenue = 0;
$r = $conn->query("SELECT c.name as cat_name, COALESCE(SUM(oi.unit_price * oi.quantity), 0) as cat_rev
    FROM order_item oi 
    JOIN product p ON oi.product_id = p.product_id 
    JOIN category c ON p.category_id = c.category_id 
    GROUP BY c.category_id, c.name 
    ORDER BY cat_rev DESC");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $category_sales[] = ['name' => $row['cat_name'], 'revenue' => (float)$row['cat_rev']];
        $total_category_revenue += (float)$row['cat_rev'];
    }
}
// If no order data, fall back to product count per category
if ($total_category_revenue == 0) {
    $category_sales = [];
    $total_cat_count = 0;
    $r = $conn->query("SELECT c.name as cat_name, COUNT(p.product_id) as prod_count 
        FROM category c LEFT JOIN product p ON c.category_id = p.category_id 
        GROUP BY c.category_id, c.name HAVING prod_count > 0 ORDER BY prod_count DESC");
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $category_sales[] = ['name' => $row['cat_name'], 'revenue' => (float)$row['prod_count']];
            $total_cat_count += (float)$row['prod_count'];
        }
    }
    $total_category_revenue = max(1, $total_cat_count);
}
// Assign colors and calculate percentages
$cat_colors = ['var(--accent)', 'var(--turquoise)', '#B8A9E0', 'var(--butter-yellow)', '#E88D72', '#7EC8E3', '#C4E87D'];
$top_categories = [];
$other_pct = 0;
foreach ($category_sales as $i => $cat) {
    $pct = round(($cat['revenue'] / $total_category_revenue) * 100);
    if ($i < 3) {
        $top_categories[] = ['name' => $cat['name'], 'pct' => $pct, 'color' => $cat_colors[$i] ?? $cat_colors[0]];
    } else {
        $other_pct += $pct;
    }
}
if ($other_pct > 0 || count($category_sales) > 3) {
    $top_categories[] = ['name' => 'Other', 'pct' => max(1, $other_pct), 'color' => $cat_colors[3]];
}
// Ensure percentages sum to 100
if (!empty($top_categories)) {
    $sum = array_sum(array_column($top_categories, 'pct'));
    if ($sum > 0 && $sum != 100) {
        $top_categories[0]['pct'] += (100 - $sum);
    }
}

// Fetch recent orders
$recent_orders = [];
$r = $conn->query("SELECT o.order_id, o.total_price, o.status, o.created_at, c.full_name FROM `order` o LEFT JOIN customer c ON o.customer_id = c.customer_id ORDER BY o.created_at DESC LIMIT 5");
if ($r) { while ($row = $r->fetch_assoc()) $recent_orders[] = $row; }

// ── Dynamic Notifications ──
$notifications = [];

// 1. Pending orders (last 48 hours)
$r = $conn->query("SELECT o.order_id, o.total_price, o.created_at, c.full_name, 
    (SELECT p.name FROM order_item oi JOIN product p ON oi.product_id = p.product_id WHERE oi.order_id = o.order_id LIMIT 1) as product_name
    FROM `order` o LEFT JOIN customer c ON o.customer_id = c.customer_id 
    WHERE o.status = 'pending' AND o.created_at >= NOW() - INTERVAL 48 HOUR 
    ORDER BY o.created_at DESC LIMIT 5");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $ago = time() - strtotime($row['created_at']);
        if ($ago < 60) $time_ago = 'Just now';
        elseif ($ago < 3600) $time_ago = floor($ago / 60) . ' min ago';
        elseif ($ago < 86400) $time_ago = floor($ago / 3600) . ' hour' . (floor($ago/3600) > 1 ? 's' : '') . ' ago';
        else $time_ago = floor($ago / 86400) . ' day' . (floor($ago/86400) > 1 ? 's' : '') . ' ago';
        $notifications[] = [
            'key' => 'order_' . $row['order_id'],
            'type' => 'order',
            'icon' => '🛒',
            'dot_class' => 'dot-order',
            'title' => 'New order',
            'detail' => htmlspecialchars($row['product_name'] ?? 'order #AA-' . $row['order_id']),
            'time' => $time_ago,
            'link' => 'admin-orders'
        ];
    }
}

// 2. Low stock products (stock <= 5)
$r = $conn->query("SELECT product_id, name, stock FROM product WHERE stock <= 5 AND stock > 0 ORDER BY stock ASC LIMIT 5");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $notifications[] = [
            'key' => 'lowstock_' . $row['product_id'],
            'type' => 'stock',
            'icon' => '📦',
            'dot_class' => 'dot-stock',
            'title' => 'Low stock alert',
            'detail' => htmlspecialchars($row['name']) . ' (' . $row['stock'] . ' left)',
            'time' => 'Action needed',
            'link' => 'admin-products'
        ];
    }
}

// 3. Out of stock products
$r = $conn->query("SELECT product_id, name FROM product WHERE stock = 0 LIMIT 3");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $notifications[] = [
            'key' => 'outstock_' . $row['product_id'],
            'type' => 'stock',
            'icon' => '⚠️',
            'dot_class' => 'dot-review',
            'title' => 'Out of stock',
            'detail' => htmlspecialchars($row['name']),
            'time' => 'Restock needed',
            'link' => 'admin-products'
        ];
    }
}

// 4. New customers (last 48 hours)
$r = $conn->query("SELECT customer_id, full_name, created_at FROM customer WHERE created_at >= NOW() - INTERVAL 48 HOUR ORDER BY created_at DESC LIMIT 3");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $ago = time() - strtotime($row['created_at']);
        if ($ago < 60) $time_ago = 'Just now';
        elseif ($ago < 3600) $time_ago = floor($ago / 60) . ' min ago';
        elseif ($ago < 86400) $time_ago = floor($ago / 3600) . ' hour' . (floor($ago/3600) > 1 ? 's' : '') . ' ago';
        else $time_ago = floor($ago / 86400) . ' day' . (floor($ago/86400) > 1 ? 's' : '') . ' ago';
        $notifications[] = [
            'key' => 'customer_' . $row['customer_id'],
            'type' => 'customer',
            'icon' => '👤',
            'dot_class' => 'dot-user',
            'title' => 'New customer',
            'detail' => htmlspecialchars($row['full_name']) . ' registered',
            'time' => $time_ago,
            'link' => 'admin-customers'
        ];
    }
}

// 5. Unread contact messages
$r = $conn->query("SELECT message_id, name, subject, submitted_at FROM contact_message ORDER BY submitted_at DESC LIMIT 3");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $ago = time() - strtotime($row['submitted_at']);
        if ($ago < 60) $time_ago = 'Just now';
        elseif ($ago < 3600) $time_ago = floor($ago / 60) . ' min ago';
        elseif ($ago < 86400) $time_ago = floor($ago / 3600) . ' hour' . (floor($ago/3600) > 1 ? 's' : '') . ' ago';
        else $time_ago = floor($ago / 86400) . ' day' . (floor($ago/86400) > 1 ? 's' : '') . ' ago';
        $notifications[] = [
            'key' => 'msg_' . $row['message_id'],
            'type' => 'message',
            'icon' => '✉️',
            'dot_class' => 'dot-review',
            'title' => 'New message',
            'detail' => htmlspecialchars($row['name']) . ' - ' . htmlspecialchars($row['subject'] ?? 'No subject'),
            'time' => $time_ago,
            'link' => 'admin-messages'
        ];
    }
}

// 6. Pending password resets
$r = $conn->query("SELECT request_id, email, requested_at FROM password_reset_request WHERE status='pending' ORDER BY requested_at DESC LIMIT 3");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $ago = time() - strtotime($row['requested_at']);
        if ($ago < 60) $time_ago = 'Just now';
        elseif ($ago < 3600) $time_ago = floor($ago / 60) . ' min ago';
        elseif ($ago < 86400) $time_ago = floor($ago / 3600) . ' hour' . (floor($ago/3600) > 1 ? 's' : '') . ' ago';
        else $time_ago = floor($ago / 86400) . ' day' . (floor($ago/86400) > 1 ? 's' : '') . ' ago';
        $notifications[] = [
            'key' => 'reset_' . $row['request_id'],
            'type' => 'reset',
            'icon' => '🔑',
            'dot_class' => 'dot-review',
            'title' => 'Password reset',
            'detail' => htmlspecialchars($row['email']) . ' requested a reset',
            'time' => $time_ago,
            'link' => 'admin-settings'
        ];
    }
}

// ── Filter out read notifications ──
$notifications = array_filter($notifications, function($n) use ($read_keys) {
    return !in_array($n['key'], $read_keys);
});
$notifications = array_values($notifications);
$notif_count = count($notifications);
$all_notif_keys = array_map(function($n) { return $n['key']; }, $notifications);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Aura Artifacts</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Playfair+Display:wght@400;600&family=Poppins:wght@300;400;500;600&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
            <style>
/* ── Admin Layout ── */
        body { background: #F4F6F9; }

        .admin-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .admin-sidebar {
            background: var(--primary);
            padding: 0;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 28px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand .brand-gem { font-size: 1.6rem; }

        .sidebar-brand-text .brand-name {
            font-family: var(--font-heading);
            color: #FFFFFF;
            font-size: 1.25rem;
            letter-spacing: 1px;
            display: block;
        }

        .sidebar-brand-text .brand-role {
            font-family: var(--font-button);
            color: var(--accent);
            font-size: 0.7rem;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .sidebar-nav {
            padding: 24px 0;
            flex: 1;
        }

        .nav-section-label {
            font-family: var(--font-button);
            font-size: 0.65rem;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.35);
            padding: 0 24px 10px;
            margin-top: 8px;
        }

        .sidebar-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: rgba(255,255,255,0.65);
            font-family: var(--font-body);
            font-size: 0.9rem;
            font-weight: 400;
            text-decoration: none;
            transition: var(--transition-fast);
            border-left: 3px solid transparent;
            cursor: pointer;
        }

        .sidebar-nav-item:hover {
            background: rgba(255,255,255,0.06);
            color: #FFFFFF;
            border-left-color: var(--accent);
        }

        .sidebar-nav-item.active {
            background: rgba(255,158,187,0.12);
            color: #FFFFFF;
            border-left-color: var(--accent);
            font-weight: 500;
        }

        .nav-icon { font-size: 1.1rem; width: 22px; text-align: center; }

        .sidebar-footer {
            padding: 20px 24px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-avatar {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--accent), var(--turquoise));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .admin-user-info .user-name {
            font-family: var(--font-body);
            color: #FFFFFF;
            font-size: 0.875rem;
            font-weight: 500;
            display: block;
        }

        .admin-user-info .user-role {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.45);
        }

        /* ── Main Content ── */
        .admin-main {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .admin-topbar {
            background: #FFFFFF;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #EBEBEB;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-title h1 {
            font-family: var(--font-heading);
            font-size: 1.6rem;
            color: var(--primary);
            margin: 0;
            line-height: 1;
        }

        .topbar-title p {
            font-size: 0.8125rem;
            color: var(--text-light);
            margin: 4px 0 0;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar-date {
            font-family: var(--font-body);
            font-size: 0.8125rem;
            color: var(--text-light);
            padding: 8px 14px;
            background: #F4F6F9;
            border-radius: 8px;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: 1.5px solid #EBEBEB;
            border-radius: 10px;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.1rem;
            transition: var(--transition-fast);
        }

        .notification-btn:hover { border-color: var(--accent); }

        .notif-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 16px;
            height: 16px;
            background: var(--accent);
            color: #FFFFFF;
            border-radius: 50%;
            font-size: 0.6rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ── Dashboard Content ── */
        .dashboard-content {
            padding: 32px;
            flex: 1;
        }

        /* ── Stats Cards ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: #FFFFFF;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            border: 1px solid #F0F0F0;
            transition: var(--transition-fast);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            user-select: none;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            border-radius: 16px 16px 0 0;
        }

        .stat-card.card-revenue::after { background: linear-gradient(90deg, var(--accent), #FF6B9D); }
        .stat-card.card-orders::after  { background: linear-gradient(90deg, var(--turquoise), #5BBFB5); }
        .stat-card.card-products::after { background: linear-gradient(90deg, #B8A9E0, #9B8DD4); }
        .stat-card.card-customers::after { background: linear-gradient(90deg, var(--butter-yellow), #FFD080); }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .stat-label {
            font-family: var(--font-button);
            font-size: 0.75rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--text-light);
            font-weight: 600;
        }

        .stat-icon-box {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .card-revenue .stat-icon-box { background: rgba(255,158,187,0.15); }
        .card-orders  .stat-icon-box { background: rgba(141,212,204,0.15); }
        .card-products .stat-icon-box { background: rgba(184,169,224,0.15); }
        .card-customers .stat-icon-box { background: rgba(255,229,180,0.25); }

        .stat-value {
            font-family: var(--font-heading);
            font-size: 2.25rem;
            color: var(--primary);
            font-weight: 700;
            line-height: 1;
            margin-bottom: 8px;
        }

        .stat-change {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.8125rem;
            font-weight: 600;
            font-family: var(--font-body);
        }

        .stat-change.positive { color: #2EAF7D; }
        .stat-change.negative { color: #E05C5C; }

        .stat-period {
            font-size: 0.75rem;
            color: var(--text-light);
            margin-left: 4px;
            font-weight: 400;
        }

        /* ── Charts Row ── */
        .charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 32px;
        }

        .dashboard-card {
            background: #FFFFFF;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            border: 1px solid #F0F0F0;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .card-header h3 {
            font-family: var(--font-subheading);
            font-size: 1.1rem;
            color: var(--primary);
            margin: 0;
        }

        .card-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            background: var(--secondary);
            color: var(--accent);
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            font-family: var(--font-button);
        }

        /* Bar chart visual (CSS-only) */
        .chart-bars {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            height: 160px;
            padding-bottom: 8px;
            border-bottom: 1.5px solid #F0F0F0;
        }

        .bar-group {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            height: 100%;
            justify-content: flex-end;
        }

        .bar {
            width: 100%;
            border-radius: 6px 6px 0 0;
            transition: opacity 0.3s ease;
            position: relative;
        }

        .bar:hover { opacity: 0.8; }

        .bar-revenue { background: linear-gradient(180deg, var(--accent), #FF6B9D); }
        .bar-orders  { background: linear-gradient(180deg, var(--turquoise), #5BBFB5); }

        .bar-label {
            font-size: 0.7rem;
            color: var(--text-light);
            font-family: var(--font-body);
            margin-top: 8px;
            text-align: center;
        }

        .chart-legend {
            display: flex;
            gap: 20px;
            margin-top: 16px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8125rem;
            color: var(--text-light);
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        /* Donut chart (CSS) */
        .donut-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .donut-chart {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            position: relative;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .donut-hole {
            position: absolute;
            inset: 25px;
            background: #FFFFFF;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .donut-hole .donut-value {
            font-family: var(--font-heading);
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
        }

        .donut-hole .donut-sub {
            font-size: 0.6rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .donut-legend {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .donut-legend-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .donut-legend-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8125rem;
            color: var(--text-light);
        }

        .donut-legend-val {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary);
        }

        /* ── Recent Activity & Orders ── */
        .bottom-row {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 20px;
        }

        /* Recent Orders Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table thead th {
            padding: 10px 14px;
            text-align: left;
            font-family: var(--font-button);
            font-size: 0.7rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--text-light);
            border-bottom: 1.5px solid #F0F0F0;
        }

        .orders-table tbody tr {
            border-bottom: 1px solid #F8F8F8;
            transition: background 0.2s ease;
        }

        .orders-table tbody tr:hover { background: #FAFAFA; }
        .orders-table tbody tr.clickable-row { cursor: pointer; }
        .orders-table tbody tr.clickable-row:hover { background: #FFF5F8; }

        .orders-table tbody td {
            padding: 12px 14px;
            font-size: 0.875rem;
            color: var(--text-dark);
        }

        .order-id {
            font-family: var(--font-button);
            font-weight: 700;
            color: var(--accent);
        }

        .order-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            font-family: var(--font-button);
        }

        .status-delivered  { background: rgba(46,175,125,0.12); color: #2EAF7D; }
        .status-processing { background: rgba(255,158,187,0.15); color: #D4547A; }
        .status-shipped    { background: rgba(141,212,204,0.2);  color: #3A9D94; }
        .status-pending    { background: rgba(255,229,180,0.4);  color: #B8860B; }

        /* Activity Feed */
        .activity-feed {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .activity-item {
            display: flex;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid #F5F5F5;
            position: relative;
        }

        .activity-item:last-child { border-bottom: none; }

        .activity-dot {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .dot-order    { background: rgba(255,158,187,0.15); }
        .dot-stock    { background: rgba(141,212,204,0.15); }
        .dot-user     { background: rgba(184,169,224,0.15); }
        .dot-review   { background: rgba(255,229,180,0.25); }

        .activity-text p {
            font-size: 0.875rem;
            color: var(--text-dark);
            margin: 0 0 3px;
            line-height: 1.4;
        }

        .activity-time {
            font-size: 0.75rem;
            color: var(--text-light);
        }

        @media (max-width: 1100px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-row { grid-template-columns: 1fr; }
            .bottom-row { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .admin-layout { grid-template-columns: 1fr; }
            .admin-sidebar { display: none; }
            .dashboard-content { padding: 16px; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }

/* ── Notification CSS-only toggle ── */
.notif-wrapper { position: relative; }
.notif-toggle-input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
}
.notif-toggle-input ~ .notif-dropdown {
    display: none;
}
.notif-toggle-input:checked ~ .notif-dropdown {
    display: block;
}
label.notification-btn {
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* ── Notification Dropdown Styles ── */
.notif-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 380px;
    background: #FFFFFF;
    border-radius: 16px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    border: 1px solid #F0F0F0;
    z-index: 999;
    animation: slideUpNotif 0.2s ease;
    overflow: hidden;
}
@keyframes slideUpNotif {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

.notif-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #F0F0F0;
}

.notif-title {
    font-family: var(--font-heading);
    font-size: 1.1rem;
    color: var(--primary);
    font-weight: 600;
}

.notif-count-label {
    font-size: 0.75rem;
    color: var(--accent);
    font-weight: 600;
    background: rgba(255,158,187,0.12);
    padding: 3px 10px;
    border-radius: 20px;
}

.notif-mark-all-btn {
    background: none;
    border: 1.5px solid #2EAF7D;
    color: #2EAF7D;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 0.72rem;
    font-weight: 700;
    cursor: pointer;
    font-family: var(--font-button);
    transition: all 0.2s;
}
.notif-mark-all-btn:hover {
    background: #2EAF7D;
    color: #FFFFFF;
}

.notif-list {
    max-height: 320px;
    overflow-y: auto;
}

.notif-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 20px;
    border-bottom: 1px solid #F8F8F8;
    text-decoration: none;
    transition: background 0.15s;
    position: relative;
}
.notif-item:hover { background: #FAFAFA; }
.notif-item.unread { background: #FFFDF5; }
.notif-item:last-child { border-bottom: none; }

.notif-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--accent);
    flex-shrink: 0;
    margin-top: 7px;
}

.notif-content {
    flex: 1;
    min-width: 0;
}
.notif-content p {
    margin: 0;
    font-size: 0.84rem;
    color: var(--text-dark);
    line-height: 1.4;
}
.notif-content p strong {
    color: var(--primary);
}
.notif-time {
    font-size: 0.72rem;
    color: var(--text-light);
    margin-top: 3px;
    display: block;
}

.notif-dismiss {
    background: none;
    border: none;
    color: #ccc;
    font-size: 0.85rem;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 6px;
    flex-shrink: 0;
    transition: all 0.15s;
    line-height: 1;
}
.notif-dismiss:hover {
    background: rgba(231,76,60,0.1);
    color: #e74c3c;
}

.notif-empty {
    text-align: center;
    padding: 32px 20px;
}
.notif-empty-icon {
    font-size: 2rem;
    display: block;
    margin-bottom: 8px;
}
.notif-empty p {
    color: var(--text-light);
    font-size: 0.88rem;
    margin: 0;
}

.notif-footer {
    padding: 12px 20px;
    border-top: 1px solid #F0F0F0;
    text-align: center;
}
.notif-footer a {
    color: var(--accent);
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    font-family: var(--font-button);
}
.notif-footer a:hover { text-decoration: underline; }

</style>
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <div class="admin-layout">

        <!-- Sidebar -->
        <aside class="admin-sidebar" aria-label="Admin navigation">
            <div class="sidebar-brand">
                <span class="brand-gem" aria-hidden="true">💎</span>
                <div class="sidebar-brand-text">
                    <span class="brand-name">Aura Artifacts</span>
                    <span class="brand-role">Admin Panel</span>
                </div>
            </div>

            <nav class="sidebar-nav" aria-label="Main admin menu">
                <p class="nav-section-label">Overview</p>
                <a href="admin-dashboard" class="sidebar-nav-item active" aria-current="page">
                    <span class="nav-icon" aria-hidden="true">📊</span> Dashboard
                </a>

                <p class="nav-section-label">Catalog</p>
                <a href="admin-products" class="sidebar-nav-item">
                    <span class="nav-icon" aria-hidden="true">💍</span> Products
                </a>

                <p class="nav-section-label">Sales</p>
                <a href="admin-orders" class="sidebar-nav-item">
                    <span class="nav-icon" aria-hidden="true">🛒</span> Orders
                </a>
                <a href="admin-customers" class="sidebar-nav-item">
                    <span class="nav-icon" aria-hidden="true">👥</span> Customers
                </a>

                <p class="nav-section-label">Communication</p>
                <a href="admin-messages" class="sidebar-nav-item">
                    <span class="nav-icon" aria-hidden="true">✉️</span> Messages
                </a>
                <a href="admin-subscribers" class="sidebar-nav-item">
                    <span class="nav-icon" aria-hidden="true">📰</span> Subscribers
                </a>

                <p class="nav-section-label">System</p>
                <a href="admin-notifications" class="sidebar-nav-item">
                    <span class="nav-icon" aria-hidden="true">🔔</span> Notifications
                    <?php if ($notif_count > 0): ?><span style="margin-left:auto;background:var(--accent);color:#fff;font-size:0.65rem;font-weight:700;padding:2px 7px;border-radius:10px;"><?= $notif_count > 9 ? '9+' : $notif_count ?></span><?php endif; ?>
                </a>
                <a href="admin-settings" class="sidebar-nav-item">
                    <span class="nav-icon" aria-hidden="true">⚙️</span> Settings
                </a>
                <a href="<?= BASE_URL ?>/" class="sidebar-nav-item" target="_blank" rel="noopener noreferrer">
                    <span class="nav-icon" aria-hidden="true">🌐</span> View Website
                </a>
                <a href="admin-logout" class="sidebar-nav-item">
                    <span class="nav-icon" aria-hidden="true">🚪</span> Logout
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="admin-user-info">
                    <div class="admin-avatar" aria-hidden="true">👤</div>
                    <div>
                        <span class="user-name"><?= htmlspecialchars($admin_name) ?></span>
                        <span class="user-role">Super Admin</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main" id="main-content">

            <!-- Top Bar -->
            <header class="admin-topbar">
                <div class="topbar-title">
                    <h1>Dashboard</h1>
                    <p>Welcome back, <?= htmlspecialchars($admin_name) ?> - here's what's happening today</p>
                </div>
                <div class="topbar-actions">
                    <span class="topbar-date" aria-label="Current date">📅 <?= date('M d, Y') ?></span>
                    <div class="notif-wrapper">
                    <input type="checkbox" id="notif-toggle" class="notif-toggle-input">
                    <label for="notif-toggle" class="notification-btn" aria-label="View <?= $notif_count ?> notifications">
                        🔔
                        <?php if ($notif_count > 0): ?>
                        <span class="notif-badge" aria-hidden="true"><?= $notif_count > 9 ? '9+' : $notif_count ?></span>
                        <?php endif; ?>
                    </label>
                    <div class="notif-dropdown" role="menu" aria-label="Notifications">
                        <div class="notif-header">
                            <span class="notif-title">Notifications</span>
                            <?php if ($notif_count > 0): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="notif_action" value="mark_all_read">
                                <input type="hidden" name="notif_keys" value='<?= json_encode($all_notif_keys) ?>'>
                                <button type="submit" class="notif-mark-all-btn">✓ Mark All Read</button>
                            </form>
                            <?php else: ?>
                            <span class="notif-count-label">0 alerts</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($notif_count === 0): ?>
                        <div class="notif-empty">
                            <span class="notif-empty-icon">✨</span>
                            <p>All clear! No new notifications.</p>
                        </div>
                        <?php else: ?>
                        <div class="notif-list">
                        <?php foreach (array_slice($notifications, 0, 8) as $notif): ?>
                        <div class="notif-item unread">
                            <div class="notif-dot"></div>
                            <a href="<?= $notif['link'] ?>" class="notif-content" style="text-decoration:none;color:inherit;">
                                <p><strong><?= $notif['title'] ?></strong> - <?= $notif['detail'] ?></p>
                                <span class="notif-time"><?= $notif['time'] ?></span>
                            </a>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="notif_action" value="mark_read">
                                <input type="hidden" name="notif_key" value="<?= $notif['key'] ?>">
                                <button type="submit" class="notif-dismiss" title="Mark as read">✕</button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <div class="notif-footer">
                            <a href="admin-notifications">View all activity →</a>
                        </div>
                    </div>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">

                <!-- Stats Cards -->
                <section aria-label="Key performance statistics">
                    <div class="stats-grid">

                        <article class="stat-card card-revenue" role="button" tabindex="0" onclick="window.location='admin-orders'" title="View revenue details">
                            <div class="stat-header">
                                <span class="stat-label">Total Revenue</span>
                                <div class="stat-icon-box" aria-hidden="true">💰</div>
                            </div>
                            <div class="stat-value">$<?= number_format($total_revenue, 0) ?></div>
                            <span class="stat-change positive">
                                💎 <span class="stat-period">from all orders</span>
                            </span>
                        </article>

                        <article class="stat-card card-orders" role="button" tabindex="0" onclick="window.location='admin-orders'" title="View all orders">
                            <div class="stat-header">
                                <span class="stat-label">Total Orders</span>
                                <div class="stat-icon-box" aria-hidden="true">🛒</div>
                            </div>
                            <div class="stat-value"><?= number_format($total_orders) ?></div>
                            <span class="stat-change positive">
                                📦 <span class="stat-period">all time</span>
                            </span>
                        </article>

                        <article class="stat-card card-products" role="button" tabindex="0" onclick="window.location='admin-products'" title="View all products">
                            <div class="stat-header">
                                <span class="stat-label">Products</span>
                                <div class="stat-icon-box" aria-hidden="true">💍</div>
                            </div>
                            <div class="stat-value"><?= number_format($total_products) ?></div>
                            <span class="stat-change positive">
                                💍 <span class="stat-period">in catalog</span>
                            </span>
                        </article>

                        <article class="stat-card card-customers" role="button" tabindex="0" onclick="window.location='admin-customers'" title="View all customers">
                            <div class="stat-header">
                                <span class="stat-label">Customers</span>
                                <div class="stat-icon-box" aria-hidden="true">👥</div>
                            </div>
                            <div class="stat-value"><?= number_format($total_customers) ?></div>
                            <span class="stat-change positive">
                                👥 <span class="stat-period">registered</span>
                            </span>
                        </article>

                    </div>
                </section>

                <!-- Charts Row -->
                <section class="charts-row" aria-label="Sales charts">

                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>Sales Overview</h3>
                            <span class="card-badge">📅 This Week</span>
                        </div>
                        <div class="chart-bars" aria-label="Bar chart showing daily sales revenue and orders for this week">
                            <?php foreach ($daily_sales as $date => $day): 
                                $rev_pct = round(($day['revenue'] / $max_revenue) * 100);
                                $ord_pct = round(($day['orders'] / $max_orders) * 100);
                                // Ensure minimum visible height when there's data
                                if ($day['revenue'] > 0 && $rev_pct < 5) $rev_pct = 5;
                                if ($day['orders'] > 0 && $ord_pct < 5) $ord_pct = 5;
                            ?>
                            <div class="bar-group">
                                <div class="bar bar-revenue" style="height:<?= $rev_pct ?>%" title="Revenue: $<?= number_format($day['revenue'], 2) ?>"></div>
                                <div class="bar bar-orders"  style="height:<?= $ord_pct ?>%" title="Orders: <?= $day['orders'] ?>"></div>
                                <span class="bar-label"><?= $day['label'] ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <div class="legend-dot" style="background:var(--accent)"></div>
                                Revenue
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot" style="background:var(--turquoise)"></div>
                                Orders
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>Top Categories</h3>
                            <span class="card-badge">📊 Sales %</span>
                        </div>
                        <div class="donut-wrapper">
                            <?php
                            // Build conic-gradient segments from real data
                            $conic_parts = [];
                            $cumulative = 0;
                            foreach ($top_categories as $cat) {
                                $start = $cumulative;
                                $cumulative += $cat['pct'];
                                $conic_parts[] = $cat['color'] . ' ' . $start . '% ' . $cumulative . '%';
                            }
                            $conic_gradient = !empty($conic_parts) ? implode(', ', $conic_parts) : 'var(--accent) 0% 100%';
                            $top_cat_name = !empty($top_categories) ? $top_categories[0]['name'] : 'N/A';
                            $top_cat_pct  = !empty($top_categories) ? $top_categories[0]['pct'] : 0;
                            // Build aria label
                            $aria_parts = [];
                            foreach ($top_categories as $cat) {
                                $aria_parts[] = htmlspecialchars($cat['name']) . ' ' . $cat['pct'] . '%';
                            }
                            $donut_aria = !empty($aria_parts) ? 'Donut chart: ' . implode(', ', $aria_parts) : 'No category data';
                            ?>
                            <div class="donut-chart" role="img" aria-label="<?= $donut_aria ?>" style="background: conic-gradient(<?= $conic_gradient ?>);">
                                <div class="donut-hole">
                                    <span class="donut-value"><?= $top_cat_pct ?>%</span>
                                    <span class="donut-sub"><?= htmlspecialchars($top_cat_name) ?></span>
                                </div>
                            </div>
                            <div class="donut-legend">
                                <?php if (empty($top_categories)): ?>
                                <p style="text-align:center;color:var(--text-light);font-size:0.85rem;">No category sales data yet</p>
                                <?php else: ?>
                                <?php foreach ($top_categories as $cat): ?>
                                <div class="donut-legend-item">
                                    <div class="donut-legend-label">
                                        <div class="legend-dot" style="background:<?= $cat['color'] ?>"></div>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </div>
                                    <span class="donut-legend-val"><?= $cat['pct'] ?>%</span>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </section>

                <!-- Bottom Row -->
                <section class="bottom-row" aria-label="Recent orders and activity">

                    <!-- Recent Orders -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>Recent Orders</h3>
                            <a href="admin-orders" class="card-badge" style="text-decoration:none;">View All →</a>
                        </div>
                        <table class="orders-table" aria-label="Recent orders list">
                            <thead>
                                <tr>
                                    <th scope="col">order ID</th>
                                    <th scope="col">customer</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_orders)): ?>
                                <tr><td colspan="4" style="text-align:center;color:var(--text-light);padding:30px;">No orders yet</td></tr>
                                <?php else: ?>
                                <?php foreach ($recent_orders as $ord): 
                                    $status_class = 'status-pending';
                                    $s = strtolower($ord['status'] ?? 'pending');
                                    if ($s === 'delivered') $status_class = 'status-delivered';
                                    elseif ($s === 'shipped') $status_class = 'status-shipped';
                                    elseif ($s === 'processing') $status_class = 'status-processing';
                                ?>
                                <tr class="clickable-row" title="View order #<?= $ord['order_id'] ?>" onclick="window.location='admin-orders?id=<?= $ord['order_id'] ?>'">
                                    <td class="order-id">#AA-<?= $ord['order_id'] ?></td>
                                    <td><?= htmlspecialchars($ord['full_name'] ?? 'Guest') ?></td>
                                    <td>$<?= number_format($ord['total_price'], 2) ?></td>
                                    <td><span class="order-status <?= $status_class ?>"><?= ucfirst($ord['status'] ?? 'Pending') ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Activity Feed -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>Recent Activity</h3>
                            <span class="card-badge">🔴 Live</span>
                        </div>
                        <div class="activity-feed" aria-label="Recent platform activity">
                            <?php if (empty($notifications)): ?>
                            <div class="activity-empty">
                                <p style="text-align:center;color:var(--text-light);padding:30px 0;">✨ No recent activity - your store is all set!</p>
                            </div>
                            <?php else: ?>
                            <?php foreach (array_slice($notifications, 0, 6) as $notif): ?>
                            <a href="<?= $notif['link'] ?>" class="activity-item activity-link">
                                <div class="activity-dot <?= $notif['dot_class'] ?>" aria-hidden="true"><?= $notif['icon'] ?></div>
                                <div class="activity-text">
                                    <p><strong><?= $notif['title'] ?></strong> - <?= $notif['detail'] ?></p>
                                    <span class="activity-time"><?= $notif['time'] ?></span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                </section>

            </div><!-- /dashboard-content -->
        </main>

    </div>

<style>
/* ── Notification Dropdown ── */
.notif-wrapper { position: relative; }

.notif-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 320px;
    background: #FFFFFF;
    border-radius: 14px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
    border: 1px solid #F0F0F0;
    z-index: 999;
    animation: dropDown 0.2s ease;
    overflow: hidden;
}

.notif-dropdown[hidden] { display: none; }

@keyframes dropDown {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}

.notif-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 18px;
    border-bottom: 1px solid #F5F5F5;
}

.notif-title {
    font-family: var(--font-button);
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 1px;
    color: var(--primary);
    text-transform: uppercase;
}

.notif-mark-all {
    background: none;
    border: none;
    font-size: 0.78rem;
    color: var(--accent);
    cursor: pointer;
    font-family: var(--font-body);
    font-weight: 500;
}

.notif-mark-all:hover { text-decoration: underline; }

.notif-count-label {
    font-size: 0.75rem;
    color: var(--text-light);
    font-family: var(--font-body);
}

.notif-empty {
    text-align: center;
    padding: 28px 18px;
    color: var(--text-light);
}

.notif-empty-icon {
    font-size: 2rem;
    display: block;
    margin-bottom: 8px;
}

.notif-empty p {
    font-size: 0.85rem;
    margin: 0;
}

.notif-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 18px;
    border-bottom: 1px solid #F8F8F8;
    text-decoration: none;
    transition: background 0.15s;
    cursor: pointer;
}

.notif-item:hover { background: #FAFBFC; }
.notif-item:last-of-type { border-bottom: none; }

.notif-item.unread { background: #FFF8FB; }
.notif-item.unread:hover { background: #FFE5EC22; }

.notif-dot {
    width: 8px; height: 8px;
    background: var(--accent);
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 6px;
}

.notif-item:not(.unread) .notif-dot { background: transparent; }

.notif-content p {
    font-size: 0.85rem;
    color: var(--primary);
    margin: 0 0 3px;
    line-height: 1.4;
}

.notif-time {
    font-size: 0.75rem;
    color: var(--text-light);
}

.notif-footer {
    padding: 12px 18px;
    text-align: center;
    border-top: 1px solid #F5F5F5;
}

.notif-footer a {
    font-size: 0.82rem;
    color: var(--accent);
    text-decoration: none;
    font-weight: 500;
}

.notif-footer a:hover { text-decoration: underline; }

/* ── Stat Cards Clickable ── */
.stat-card[role="button"] { cursor: pointer; }
.stat-card[role="button"]:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
.stat-card[role="button"]:focus { outline: 2px solid var(--accent); outline-offset: 3px; }

/* ── Activity Link ── */
.activity-link {
    text-decoration: none;
    color: inherit;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid #F5F7FA;
    transition: background 0.15s;
    border-radius: 8px;
    padding: 10px 8px;
    margin: 0 -8px;
}
.activity-link:hover { background: #FAFBFC; }
</style>




<!-- order Detail Modal -->
<div class="modal-overlay" id="order-modal" hidden role="dialog" aria-modal="true" aria-labelledby="order-modal-title">
  <div class="modal-box wide-modal">
    <button class="modal-close" onclick="document.getElementById('order-modal').hidden=true" aria-label="Close">✕</button>
    <div class="modal-icon">🛒</div>
    <h2 class="modal-title" id="order-modal-title">order Details</h2>
    <div class="order-detail-grid">
      <div class="detail-section">
        <h3>👤 customer Info</h3>
        <p><strong>Name:</strong> <span id="om-name"></span></p>
        <p><strong>Email:</strong> <span id="om-email"></span></p>
        <p><strong>Phone:</strong> <span id="om-phone"></span></p>
      </div>
      <div class="detail-section">
        <h3>📦 order Info</h3>
        <p><strong>order ID:</strong> <span id="om-id"></span></p>
        <p><strong>Date:</strong> <span id="om-date"></span></p>
        <p><strong>product:</strong> <span id="om-product"></span></p>
        <p><strong>Amount:</strong> <span id="om-amount"></span></p>
        <p><strong>Status:</strong> <span id="om-status"></span></p>
        <p><strong>Payment:</strong> <span id="om-pay"></span></p>
      </div>
    </div>
    <div style="display:flex;gap:12px;margin-top:8px;">
      <a href="admin-orders.php" class="modal-btn-primary" style="text-decoration:none;text-align:center;">View in Orders →</a>
      <button class="modal-btn-secondary" onclick="document.getElementById('order-modal').hidden=true">Close</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="toast" id="dash-toast" hidden role="status" aria-live="polite"></div>

<style>
/* ── Quick Actions ── */
.quick-actions-section { margin-bottom: 24px; }
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}
.quick-action-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: #FFFFFF;
    border-radius: 12px;
    border: 1.5px solid #E8EDF2;
    text-decoration: none;
    color: var(--primary);
    font-family: var(--font-button);
    font-size: 0.85rem;
    font-weight: 600;
    transition: var(--transition-fast);
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.quick-action-card:hover {
    border-color: var(--accent);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255,158,187,0.2);
    color: var(--primary);
}
.qa-icon { font-size: 1.3rem; }
.qa-label { flex: 1; }
.qa-arrow { color: var(--accent); font-size: 1rem; }

/* ── Clickable rows ── */
.clickable-row { cursor: pointer; transition: background 0.15s; }
.clickable-row:hover { background: #FFF8FB !important; }

/* ── Modal ── */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex; align-items: center; justify-content: center;
    z-index: 1000; padding: 20px;
    backdrop-filter: blur(4px);
    animation: fadeInM 0.2s ease;
}
.modal-overlay[hidden] { display: none; }
@keyframes fadeInM { from{opacity:0;} to{opacity:1;} }

.modal-box {
    background: #FFFFFF; border-radius: 20px;
    padding: 36px 32px; width: 100%; max-width: 440px;
    position: relative; box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    animation: slideUpM 0.25s ease;
}
.wide-modal { max-width: 580px; }
@keyframes slideUpM {
    from { transform: translateY(16px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

.modal-close {
    position: absolute; top: 14px; right: 18px;
    background: none; border: none; font-size: 1.2rem;
    color: var(--text-light); cursor: pointer;
    padding: 4px 8px; border-radius: 6px;
}
.modal-close:hover { background: #F5F5F5; }

.modal-icon  { font-size: 2.2rem; text-align: center; margin-bottom: 12px; }
.modal-title { font-family: var(--font-heading); font-size: 1.6rem; color: var(--primary); text-align: center; margin-bottom: 20px; }

.order-detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
.detail-section h3 { font-family: var(--font-heading); font-size: 1rem; color: var(--primary); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #F0F0F0; }
.detail-section p  { font-size: 0.875rem; color: var(--text-light); margin-bottom: 8px; }
.detail-section p strong { color: var(--primary); }

.modal-btn-primary {
    flex: 1; padding: 13px;
    background: var(--primary); color: #FFF;
    border: none; border-radius: 12px;
    font-family: var(--font-button); font-size: 0.875rem; font-weight: 700;
    letter-spacing: 1px; cursor: pointer; transition: var(--transition-smooth);
}
.modal-btn-primary:hover { background: var(--accent); }

.modal-btn-secondary {
    flex: 1; padding: 13px;
    background: #F4F6F9; color: var(--primary);
    border: none; border-radius: 12px;
    font-family: var(--font-button); font-size: 0.875rem; font-weight: 600;
    cursor: pointer; transition: var(--transition-fast);
}
.modal-btn-secondary:hover { background: #E8EDF2; }

/* Chart bars tooltip */
.bar[title]:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%; left: 50%; transform: translateX(-50%);
    background: var(--primary); color: #FFF;
    padding: 4px 10px; border-radius: 6px;
    font-size: 0.75rem; white-space: nowrap;
    z-index: 10; margin-bottom: 4px;
    font-family: var(--font-body);
}
.bar { position: relative; }

/* Toast */
.toast {
    position: fixed; bottom: 28px; right: 28px;
    background: var(--primary); color: #FFF;
    padding: 14px 22px; border-radius: 12px;
    font-size: 0.9rem; font-family: var(--font-body);
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    z-index: 9999; animation: slideUpM 0.3s ease;
    max-width: 300px;
}
.toast[hidden] { display: none; }
.toast.success { background: #2EAF7D; }
</style>

<script>
function openOrderModal(id, name, amount, status, email, phone, date, product, payment) {
    document.getElementById('om-id').textContent = id;
    document.getElementById('om-name').textContent = name;
    document.getElementById('om-amount').textContent = amount;
    document.getElementById('om-status').textContent = status;
    document.getElementById('om-email').textContent = email || '-';
    document.getElementById('om-phone').textContent = phone || '-';
    document.getElementById('om-date').textContent = date || '-';
    document.getElementById('om-product').textContent = product || '-';
    document.getElementById('om-pay').textContent = payment || '-';
    document.getElementById('order-modal').hidden = false;
}

document.getElementById('order-modal')?.addEventListener('click', function(e) {
    if (e.target === this) this.hidden = true;
});
</script>

</body>
</html>
