<?php
// ============================================================
// admin-notifications.php - All Notifications (Read + Unread)
// Shows complete notification history with filtering
// ============================================================
require_once '../config.php';
require_once 'admin-auth.php';

$admin_id = (int)$_SESSION['admin_id'];

// Ensure table exists
$conn->query("CREATE TABLE IF NOT EXISTS `notification_read` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `notif_key` varchar(100) NOT NULL,
  `read_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_read` (`admin_id`, `notif_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Handle actions
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
    if ($_POST['notif_action'] === 'unread' && !empty($_POST['notif_key'])) {
        $key = $_POST['notif_key'];
        $stmt = $conn->prepare("DELETE FROM notification_read WHERE admin_id=? AND notif_key=?");
        $stmt->bind_param("is", $admin_id, $key);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin-notifications" . (isset($_GET['filter']) ? '?filter=' . $_GET['filter'] : '')); exit();
}

// Load read keys
$read_keys = [];
$r = $conn->query("SELECT notif_key FROM notification_read WHERE admin_id = $admin_id");
if ($r) { while ($row = $r->fetch_assoc()) $read_keys[] = $row['notif_key']; }

// ── Build ALL notifications ──
$notifications = [];

// Helper
function timeAgo($timestamp) {
    $ago = time() - strtotime($timestamp);
    if ($ago < 60) return 'Just now';
    if ($ago < 3600) return floor($ago / 60) . ' min ago';
    if ($ago < 86400) return floor($ago / 3600) . ' hour' . (floor($ago/3600) > 1 ? 's' : '') . ' ago';
    if ($ago < 604800) return floor($ago / 86400) . ' day' . (floor($ago/86400) > 1 ? 's' : '') . ' ago';
    return date('d M Y', strtotime($timestamp));
}

// 1. Orders (pending, last 7 days)
$r = $conn->query("SELECT o.order_id, o.total_price, o.created_at, c.full_name, 
    (SELECT p.name FROM order_item oi JOIN product p ON oi.product_id = p.product_id WHERE oi.order_id = o.order_id LIMIT 1) as product_name
    FROM `order` o LEFT JOIN customer c ON o.customer_id = c.customer_id 
    WHERE o.status = 'pending' AND o.created_at >= NOW() - INTERVAL 7 DAY 
    ORDER BY o.created_at DESC LIMIT 20");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $notifications[] = [
            'key' => 'order_' . $row['order_id'],
            'type' => 'order',
            'icon' => '🛒',
            'color' => '#FF9EBB',
            'title' => 'New order #AA-' . $row['order_id'],
            'detail' => ($row['full_name'] ?? 'Guest') . ' ordered ' . ($row['product_name'] ?? 'items') . ' - $' . number_format($row['total_price'], 2),
            'time' => timeAgo($row['created_at']),
            'timestamp' => $row['created_at'],
            'link' => 'admin-orders'
        ];
    }
}

// 2. Low stock
$r = $conn->query("SELECT product_id, name, stock FROM product WHERE stock <= 5 AND stock > 0 ORDER BY stock ASC LIMIT 10");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $notifications[] = [
            'key' => 'lowstock_' . $row['product_id'],
            'type' => 'stock',
            'icon' => '📦',
            'color' => '#8DD4CC',
            'title' => 'Low Stock Alert',
            'detail' => $row['name'] . ' - only ' . $row['stock'] . ' left in stock',
            'time' => 'Action needed',
            'timestamp' => date('Y-m-d H:i:s'),
            'link' => 'admin-products'
        ];
    }
}

// 3. Out of stock
$r = $conn->query("SELECT product_id, name FROM product WHERE stock = 0 LIMIT 10");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $notifications[] = [
            'key' => 'outstock_' . $row['product_id'],
            'type' => 'stock',
            'icon' => '⚠️',
            'color' => '#E74C3C',
            'title' => 'Out of Stock',
            'detail' => $row['name'] . ' is completely out of stock',
            'time' => 'Restock needed',
            'timestamp' => date('Y-m-d H:i:s'),
            'link' => 'admin-products'
        ];
    }
}

// 4. New customers (last 7 days)
$r = $conn->query("SELECT customer_id, full_name, email, created_at FROM customer WHERE created_at >= NOW() - INTERVAL 7 DAY ORDER BY created_at DESC LIMIT 10");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $notifications[] = [
            'key' => 'customer_' . $row['customer_id'],
            'type' => 'customer',
            'icon' => '👤',
            'color' => '#B8A9E0',
            'title' => 'New customer',
            'detail' => $row['full_name'] . ' (' . $row['email'] . ') registered',
            'time' => timeAgo($row['created_at']),
            'timestamp' => $row['created_at'],
            'link' => 'admin-customers'
        ];
    }
}

// 5. Contact messages (last 30 days)
$r = $conn->query("SELECT message_id, name, email, subject, submitted_at FROM contact_message ORDER BY submitted_at DESC LIMIT 15");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $notifications[] = [
            'key' => 'msg_' . $row['message_id'],
            'type' => 'message',
            'icon' => '✉️',
            'color' => '#FFE5B4',
            'title' => 'New Message',
            'detail' => $row['name'] . ' - ' . ($row['subject'] ?? 'No subject'),
            'time' => timeAgo($row['submitted_at']),
            'timestamp' => $row['submitted_at'],
            'link' => 'admin-messages'
        ];
    }
}

// 6. Password resets
$r = $conn->query("SELECT request_id, email, user_type, status, requested_at FROM password_reset_request ORDER BY requested_at DESC LIMIT 10");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $notifications[] = [
            'key' => 'reset_' . $row['request_id'],
            'type' => 'reset',
            'icon' => '🔑',
            'color' => '#D4AF37',
            'title' => 'Password Reset Request',
            'detail' => $row['email'] . ' (' . ucfirst($row['user_type']) . ') - ' . ucfirst($row['status']),
            'time' => timeAgo($row['requested_at']),
            'timestamp' => $row['requested_at'],
            'link' => 'admin-settings'
        ];
    }
}

// Mark read status
foreach ($notifications as &$n) {
    $n['is_read'] = in_array($n['key'], $read_keys);
}
unset($n);

// Filter
$filter = $_GET['filter'] ?? 'all';
$filtered = $notifications;
if ($filter !== 'all') {
    $filtered = array_filter($notifications, function($n) use ($filter) {
        return $n['type'] === $filter;
    });
    $filtered = array_values($filtered);
}

$unread_count = count(array_filter($notifications, function($n) { return !$n['is_read']; }));
$unread_keys = array_values(array_map(function($n) { return $n['key']; }, array_filter($notifications, function($n) { return !$n['is_read']; })));

// Count by type
$type_counts = ['all' => count($notifications)];
foreach ($notifications as $n) {
    $type_counts[$n['type']] = ($type_counts[$n['type']] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <title>Notifications - Aura Artifacts Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;}
        body{background:#f0f2f5;font-family:'Poppins',sans-serif;display:flex;min-height:100vh;margin:0;}
        .sidebar{width:240px;background:#2d3e50;min-height:100vh;padding:30px 20px;flex-shrink:0;}
        .sidebar-logo{font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:#fff;margin-bottom:32px;display:block;text-decoration:none;}
        .sidebar a{display:block;color:rgba(255,255,255,0.7);text-decoration:none;padding:10px 14px;border-radius:8px;margin-bottom:4px;font-size:0.88rem;}
        .sidebar a:hover,.sidebar a.active{background:rgba(255,255,255,0.1);color:#fff;}
        .sidebar .logout{color:#ff9ebb;margin-top:24px;}
        .main{flex:1;padding:32px;max-width:1000px;}
        .page-title{font-family:'Cormorant Garamond',serif;font-size:2rem;color:#2d3e50;margin-bottom:6px;}
        .page-sub{color:#888;font-size:0.88rem;margin-bottom:24px;}

        /* Stats */
        .stat-bar{display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;}
        .stat-pill{background:#fff;padding:8px 18px;border-radius:20px;font-size:0.82rem;font-weight:600;color:#666;box-shadow:0 2px 8px rgba(0,0,0,0.04);display:flex;align-items:center;gap:6px;}
        .stat-pill strong{color:#2d3e50;font-size:1rem;}

        /* Filter Tabs */
        .filter-tabs{display:flex;gap:6px;margin-bottom:20px;flex-wrap:wrap;}
        .filter-tab{padding:8px 18px;border-radius:20px;font-size:0.8rem;font-weight:600;border:1.5px solid #e0e0e0;background:#fff;color:#666;cursor:pointer;text-decoration:none;transition:all 0.2s;display:flex;align-items:center;gap:5px;}
        .filter-tab:hover{border-color:#D4AF37;color:#D4AF37;}
        .filter-tab.active{background:#2d3e50;color:#fff;border-color:#2d3e50;}
        .filter-tab .count{background:rgba(0,0,0,0.1);padding:1px 8px;border-radius:10px;font-size:0.72rem;}
        .filter-tab.active .count{background:rgba(255,255,255,0.2);}

        /* Action bar */
        .action-bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;}
        .btn{padding:8px 18px;border:none;border-radius:8px;cursor:pointer;font-size:0.82rem;font-weight:600;transition:all 0.2s;}
        .btn-green{background:#2EAF7D;color:#fff;}
        .btn-green:hover{background:#259a6c;}

        /* Notification Cards */
        .notif-card{display:flex;align-items:flex-start;gap:14px;padding:16px 20px;background:#fff;border-radius:12px;margin-bottom:8px;border:1px solid #f0f0f0;transition:all 0.2s;box-shadow:0 1px 4px rgba(0,0,0,0.03);}
        .notif-card:hover{box-shadow:0 4px 16px rgba(0,0,0,0.06);}
        .notif-card.unread{border-left:4px solid #D4AF37;background:#fffdf5;}
        .notif-card.read{opacity:0.6;}
        .notif-card.read:hover{opacity:0.85;}

        .notif-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;}
        .notif-body{flex:1;min-width:0;}
        .notif-title-text{font-weight:600;font-size:0.9rem;color:#2d3e50;margin-bottom:3px;}
        .notif-detail{font-size:0.84rem;color:#666;line-height:1.5;margin-bottom:4px;}
        .notif-meta{display:flex;gap:12px;align-items:center;font-size:0.75rem;color:#999;}
        .notif-type-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;}

        .notif-actions{display:flex;gap:6px;flex-shrink:0;align-items:center;}
        .notif-actions form{display:inline;}
        .btn-sm{padding:5px 12px;font-size:0.75rem;border-radius:6px;}
        .btn-outline{background:none;border:1.5px solid #ddd;color:#888;}
        .btn-outline:hover{border-color:#2EAF7D;color:#2EAF7D;}
        .btn-link{background:none;border:none;color:#3498db;font-size:0.78rem;font-weight:600;cursor:pointer;text-decoration:none;padding:5px 10px;border-radius:6px;}
        .btn-link:hover{background:rgba(52,152,219,0.08);}

        .read-badge{font-size:0.7rem;color:#2EAF7D;font-weight:600;display:flex;align-items:center;gap:3px;}

        .empty-state{text-align:center;padding:60px 20px;color:#999;}
        .empty-state .icon{font-size:3rem;margin-bottom:12px;display:block;}
        .empty-state p{font-size:0.95rem;}
    </style>
</head>
<body>
<div class="sidebar">
    <a href="admin-dashboard" class="sidebar-logo">AURA ARTIFACTS Admin</a>
    <a href="admin-dashboard">📊 Dashboard</a>
    <a href="admin-products">📦 Products</a>
    <a href="admin-orders">📋 Orders</a>
    <a href="admin-customers">👥 Customers</a>
    <a href="admin-messages">✉️ Messages</a>
    <a href="admin-subscribers">📰 Subscribers</a>
    <a href="admin-notifications" class="active">🔔 Notifications</a>
    <a href="admin-settings">⚙️ Settings</a>
    <a href="admin-logout" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <h1 class="page-title">🔔 All Notifications</h1>
    <p class="page-sub">View and manage all system alerts and activity</p>

    <div class="stat-bar">
        <div class="stat-pill">📬 Total: <strong><?= count($notifications) ?></strong></div>
        <div class="stat-pill">🔴 Unread: <strong><?= $unread_count ?></strong></div>
        <div class="stat-pill">✅ Read: <strong><?= count($notifications) - $unread_count ?></strong></div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="admin-notifications" class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>">All <span class="count"><?= $type_counts['all'] ?></span></a>
        <a href="admin-notifications?filter=order" class="filter-tab <?= $filter === 'order' ? 'active' : '' ?>">🛒 Orders <span class="count"><?= $type_counts['order'] ?? 0 ?></span></a>
        <a href="admin-notifications?filter=stock" class="filter-tab <?= $filter === 'stock' ? 'active' : '' ?>">📦 Stock <span class="count"><?= $type_counts['stock'] ?? 0 ?></span></a>
        <a href="admin-notifications?filter=customer" class="filter-tab <?= $filter === 'customer' ? 'active' : '' ?>">👤 Customers <span class="count"><?= $type_counts['customer'] ?? 0 ?></span></a>
        <a href="admin-notifications?filter=message" class="filter-tab <?= $filter === 'message' ? 'active' : '' ?>">✉️ Messages <span class="count"><?= $type_counts['message'] ?? 0 ?></span></a>
        <a href="admin-notifications?filter=reset" class="filter-tab <?= $filter === 'reset' ? 'active' : '' ?>">🔑 Resets <span class="count"><?= $type_counts['reset'] ?? 0 ?></span></a>
    </div>

    <!-- Action Bar -->
    <?php if ($unread_count > 0): ?>
    <div class="action-bar">
        <span style="font-size:0.85rem;color:#888;"><?= count($filtered) ?> notification<?= count($filtered) !== 1 ? 's' : '' ?></span>
        <form method="POST">
            <input type="hidden" name="notif_action" value="mark_all_read">
            <input type="hidden" name="notif_keys" value='<?= json_encode($unread_keys) ?>'>
            <button type="submit" class="btn btn-green">✓ Mark All as Read</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Notification List -->
    <?php if (empty($filtered)): ?>
    <div class="empty-state">
        <span class="icon">✨</span>
        <p>No notifications<?= $filter !== 'all' ? ' in this category' : '' ?>.</p>
    </div>
    <?php else: ?>
        <?php foreach ($filtered as $n): ?>
        <div class="notif-card <?= $n['is_read'] ? 'read' : 'unread' ?>">
            <div class="notif-icon" style="background:<?= $n['color'] ?>20;">
                <?= $n['icon'] ?>
            </div>
            <div class="notif-body">
                <div class="notif-title-text"><?= $n['title'] ?></div>
                <div class="notif-detail"><?= htmlspecialchars($n['detail']) ?></div>
                <div class="notif-meta">
                    <span>🕐 <?= $n['time'] ?></span>
                    <span class="notif-type-badge" style="background:<?= $n['color'] ?>20;color:<?= $n['color'] ?>;"><?= $n['type'] ?></span>
                    <?php if ($n['is_read']): ?>
                    <span class="read-badge">✓ Read</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="notif-actions">
                <a href="<?= $n['link'] ?>" class="btn-link">View →</a>
                <?php if (!$n['is_read']): ?>
                <form method="POST">
                    <input type="hidden" name="notif_action" value="mark_read">
                    <input type="hidden" name="notif_key" value="<?= $n['key'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm" title="Mark as read">✓ Read</button>
                </form>
                <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="notif_action" value="unread">
                    <input type="hidden" name="notif_key" value="<?= $n['key'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm" title="Mark as unread" style="border-color:#D4AF37;color:#D4AF37;">↩ Unread</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
