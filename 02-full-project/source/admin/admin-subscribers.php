<?php
// ============================================================
// admin-subscribers.php - newsletter Subscribers Management
// View newsletter subscribers from "Sign Up Today!" form
// ============================================================
require_once '../config.php';
require_once 'admin-auth.php';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete') {
        $id = (int)$_POST['newsletter_id'];
        $stmt = $conn->prepare("DELETE FROM newsletter WHERE newsletter_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin-subscribers"); exit();
}

// Fetch subscribers
$subscribers = $conn->query("SELECT * FROM newsletter ORDER BY subscribed_at DESC");
$total = $subscribers ? $subscribers->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <title>newsletter Subscribers - Aura Artifacts Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;}
        body{background:#f0f2f5;font-family:'Poppins',sans-serif;display:flex;min-height:100vh;margin:0;}
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
        td{padding:12px 14px;border-bottom:1px solid #f0f0f0;font-size:0.88rem;}
        tr:last-child td{border-bottom:none;}
        tr:hover td{background:#fafafa;}
        .btn{padding:9px 20px;border:none;border-radius:8px;cursor:pointer;font-size:0.85rem;font-weight:600;transition:all 0.2s;}
        .btn-sm{padding:6px 14px;font-size:0.78rem;}
        .btn-red{background:#e74c3c;color:#fff;}
        .btn-red:hover{background:#c0392b;}
        .btn-gold{background:#D4AF37;color:#fff;}
        .stat-bar{display:flex;gap:16px;margin-bottom:18px;}
        .stat-item{background:#f8f9fa;padding:12px 20px;border-radius:10px;display:flex;align-items:center;gap:8px;font-size:0.9rem;}
        .stat-item strong{color:#2d3e50;font-size:1.1rem;}
        .email-col{color:#3498db;font-weight:500;}
        .empty-msg{text-align:center;color:#999;padding:40px;}
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
    <a href="admin-subscribers" class="active">📰 Subscribers</a>
    <a href="admin-settings">⚙️ Settings</a>
    <a href="admin-logout" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <h1 class="page-title">📰 newsletter Subscribers</h1>

    <div class="stat-bar">
        <div class="stat-item">📧 Total Subscribers: <strong><?= $total ?></strong></div>
    </div>

    <div class="card">
        <h2>All Subscribers</h2>
        
        <?php if ($total > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Email</th>
                    <th>Subscribed At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; while ($s = $subscribers->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td class="email-col"><?= htmlspecialchars($s['email']) ?></td>
                    <td><?= date('d M Y, H:i', strtotime($s['subscribed_at'])) ?></td>
                    <td>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this subscriber?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="newsletter_id" value="<?= $s['newsletter_id'] ?>">
                            <button type="submit" class="btn btn-red btn-sm">🗑 Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="empty-msg">No subscribers yet. Subscribers from the "Sign Up Today!" form will appear here.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
