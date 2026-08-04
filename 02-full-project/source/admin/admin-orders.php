<?php
// ============================================================
// admin-orders.php - Manage Orders (Task 10)
// Author: Team Member
// ============================================================
require_once '../config.php';
require_once 'admin-auth.php';

$admin_id = (int)$_SESSION['admin_id'];
$msg = '';

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];
    $status   = trim($_POST['status']);
    // Prepared statement: update order status
    $stmt = $conn->prepare("UPDATE `order` SET status = ?, processed_by = ? WHERE order_id = ?");
    $stmt->bind_param("sii", $status, $admin_id, $order_id);
    $stmt->execute();
    $stmt->close();
    $msg = "order #" . str_pad($order_id,6,'0',STR_PAD_LEFT) . " updated to '$status'.";
}

// View single order
$view_order   = null;
$order_items  = [];
if (isset($_GET['id'])) {
    $oid = (int)$_GET['id'];
    // Prepared statement: get order with customer info
    $stmt = $conn->prepare("SELECT o.*, c.full_name, c.email, c.phone FROM `order` o LEFT JOIN customer c ON o.customer_id=c.customer_id WHERE o.order_id = ?");
    $stmt->bind_param("i", $oid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $view_order  = $res->fetch_assoc();
        $stmt->close();
        // Prepared statement: get order items
        $stmt = $conn->prepare("SELECT oi.*, p.name, p.image_url FROM order_item oi LEFT JOIN product p ON oi.product_id=p.product_id WHERE oi.order_id = ?");
        $stmt->bind_param("i", $oid);
        $stmt->execute();
        $items_res = $stmt->get_result();
        while ($i = $items_res->fetch_assoc()) $order_items[] = $i;
    }
    $stmt->close();
}

// Search / filter with prepared statements
$search     = trim($_GET['search'] ?? '');
$filter_st  = trim($_GET['status'] ?? '');

// Build query dynamically based on filters
$sql = "SELECT o.*, c.full_name FROM `order` o LEFT JOIN customer c ON o.customer_id=c.customer_id WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $sql .= " AND (c.full_name LIKE ? OR o.order_id LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}
if ($filter_st) {
    $sql .= " AND o.status = ?";
    $params[] = $filter_st;
    $types .= "s";
}
$sql .= " ORDER BY o.created_at DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $orders = $stmt->get_result();
} else {
    $orders = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <title>Manage Orders - Aura Artifacts Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;}
        body{background:#f0f2f5;font-family:'Poppins',sans-serif;display:flex;min-height:100vh;}
        .sidebar{width:240px;background:#2d3e50;min-height:100vh;padding:30px 20px;flex-shrink:0;}
        .sidebar-logo{font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:#fff;margin-bottom:32px;display:block;text-decoration:none;}
        .sidebar a{display:block;color:rgba(255,255,255,0.7);text-decoration:none;padding:10px 14px;border-radius:8px;margin-bottom:4px;font-size:0.88rem;}
        .sidebar a:hover,.sidebar a.active{background:rgba(255,255,255,0.1);color:#fff;}
        .sidebar .logout{color:#ff9ebb;margin-top:24px;}
        .main{flex:1;padding:32px;}
        .page-title{font-family:'Cormorant Garamond',serif;font-size:2rem;color:#2d3e50;margin-bottom:24px;}
        .card{background:#fff;border-radius:16px;padding:28px;box-shadow:0 2px 12px rgba(0,0,0,0.06);margin-bottom:24px;}
        .card h2{font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:#2d3e50;margin-bottom:18px;}
        table{width:100%;border-collapse:collapse;}
        th{background:#f0f2f5;padding:10px 14px;text-align:left;font-size:0.75rem;text-transform:uppercase;letter-spacing:1px;color:#666;}
        td{padding:12px 14px;border-bottom:1px solid #f0f0f0;font-size:0.88rem;vertical-align:middle;}
        tr:last-child td{border-bottom:none;}
        .badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;}
        .badge-pending{background:#fff3cd;color:#856404;}
        .badge-completed{background:#d4edda;color:#155724;}
        .badge-cancelled{background:#f8d7da;color:#721c24;}
        .badge-processing{background:#cce5ff;color:#004085;}
        .filter-row{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px;}
        .filter-row input,.filter-row select{padding:9px 14px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:0.88rem;font-family:'Poppins',sans-serif;}
        .btn{padding:9px 20px;border:none;border-radius:8px;cursor:pointer;font-size:0.85rem;font-weight:600;}
        .btn-gold{background:#D4AF37;color:#fff;}
        .btn-blue{background:#3498db;color:#fff;}
        .alert-success{background:#e8f8f5;color:#27ae60;border-radius:8px;padding:12px;margin-bottom:16px;}
        /* order detail panel */
        .order-detail{border:1.5px solid #e0e0e0;border-radius:12px;padding:24px;margin-bottom:24px;}
        .detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;}
        .detail-item .label{font-size:0.72rem;text-transform:uppercase;letter-spacing:1px;color:#888;font-weight:600;}
        .detail-item .value{font-size:0.92rem;color:#2d3e50;margin-top:3px;}
        .item-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f0e8d8;}
        .item-row:last-child{border-bottom:none;}
        .item-thumb{width:44px;height:44px;object-fit:cover;border-radius:6px;}
        select.status-sel{padding:6px 10px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:0.85rem;}
    </style>
</head>
<body>
<div class="sidebar">
    <a href="admin-dashboard.php" class="sidebar-logo">AURA ARTIFACTS Admin</a>
    <a href="admin-dashboard.php">📊 Dashboard</a>
    <a href="admin-products.php">📦 Products</a>
    <a href="admin-orders.php" class="active">📋 Orders</a>
    <a href="admin-customers.php">👥 Customers</a>
    <a href="admin-logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <h1 class="page-title">Manage Orders</h1>
    <?php if ($msg): ?><div class="alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <!-- Single order Detail -->
    <?php if ($view_order): ?>
    <div class="card">
        <h2>order #<?= str_pad($view_order['order_id'],6,'0',STR_PAD_LEFT) ?> Details</h2>
        <div class="order-detail">
            <div class="detail-grid">
                <div class="detail-item"><div class="label">customer</div><div class="value"><?= htmlspecialchars($view_order['full_name']) ?></div></div>
                <div class="detail-item"><div class="label">Email</div><div class="value"><?= htmlspecialchars($view_order['email']) ?></div></div>
                <div class="detail-item"><div class="label">Phone</div><div class="value"><?= htmlspecialchars($view_order['phone'] ?? 'N/A') ?></div></div>
                <div class="detail-item"><div class="label">Address</div><div class="value"><?= htmlspecialchars($view_order['address'] ?? 'N/A') ?></div></div>
                <div class="detail-item"><div class="label">Payment</div><div class="value"><?= ucfirst($view_order['payment']) ?></div></div>
                <div class="detail-item"><div class="label">Date</div><div class="value"><?= date('d M Y, H:i', strtotime($view_order['created_at'])) ?></div></div>
            </div>

            <h3 style="margin-bottom:12px; color:#2d3e50;">Items Ordered</h3>
            <?php foreach ($order_items as $item): ?>
            <div class="item-row">
                <img src="../<?= htmlspecialchars($item['image_url'] ?? 'assets/images/placeholder.png') ?>" alt="" class="item-thumb" onerror="this.src='../assets/images/placeholder.png'">
                <div style="flex:1;"><?= htmlspecialchars($item['name'] ?? 'product') ?> <span style="color:#888;">× <?= $item['quantity'] ?></span></div>
                <div style="color:#D4AF37; font-weight:700;">$<?= number_format($item['unit_price'] * $item['quantity'],2) ?></div>
            </div>
            <?php endforeach; ?>

            <div style="text-align:right; margin-top:14px; font-size:1.1rem; font-family:'Cormorant Garamond',serif; color:#2d3e50;">
                Total: <strong>$<?= number_format($view_order['total_price'],2) ?></strong>
            </div>

            <!-- Update Status -->
            <form method="POST" style="margin-top:20px; display:flex; gap:12px; align-items:center;">
                <input type="hidden" name="order_id" value="<?= $view_order['order_id'] ?>">
                <label style="font-size:0.85rem; color:#666;">Update Status:</label>
                <select name="status" class="status-sel">
                    <?php foreach (['pending','processing','completed','cancelled'] as $st): ?>
                        <option value="<?= $st ?>" <?= $view_order['status']===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-gold">Save</button>
                <a href="admin-orders.php" class="btn btn-blue" style="text-decoration:none;">← Back</a>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Orders Table -->
    <div class="card">
        <h2>All Orders</h2>
        <form method="GET" class="filter-row">
            <input type="text" name="search" placeholder="Search by name or order #" value="<?= htmlspecialchars($search) ?>">
            <select name="status">
                <option value="">All Statuses</option>
                <?php foreach (['pending','processing','completed','cancelled'] as $st): ?>
                    <option value="<?= $st ?>" <?= $filter_st===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-gold">Filter</button>
            <?php if ($search || $filter_st): ?><a href="admin-orders.php" class="btn btn-blue" style="text-decoration:none;">Clear</a><?php endif; ?>
        </form>

        <table>
            <thead><tr><th>order #</th><th>customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
            <?php while ($o = $orders->fetch_assoc()):
                $s = $o['status'];
                $cls = "badge-$s";
            ?>
            <tr>
                <td>#<?= str_pad($o['order_id'],6,'0',STR_PAD_LEFT) ?></td>
                <td><?= htmlspecialchars($o['full_name'] ?? 'N/A') ?></td>
                <td>$<?= number_format($o['total_price'],2) ?></td>
                <td><?= ucfirst($o['payment']) ?></td>
                <td><span class="badge <?= $cls ?>"><?= ucfirst($s) ?></span></td>
                <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                <td><a href="admin-orders.php?id=<?= $o['order_id'] ?>" style="color:#D4AF37; text-decoration:none; font-size:0.82rem;">View →</a></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
