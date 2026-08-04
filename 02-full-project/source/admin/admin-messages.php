<?php
// ============================================================
// admin-messages.php - Contact Messages Management
// View and manage contact form submissions
// ============================================================
require_once '../config.php';
require_once 'admin-auth.php';

// Handle mark as read/unread
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'mark_read') {
        $id = (int)$_POST['message_id'];
        $stmt = $conn->prepare("UPDATE contact_message SET is_read=1 WHERE message_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    if ($_POST['action'] === 'mark_all_read') {
        $conn->query("UPDATE contact_message SET is_read=1 WHERE is_read=0");
    }
    if ($_POST['action'] === 'delete') {
        $id = (int)$_POST['message_id'];
        $stmt = $conn->prepare("DELETE FROM contact_message WHERE message_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin-messages"); exit();
}

// Fetch messages
$messages = $conn->query("SELECT * FROM contact_message ORDER BY submitted_at DESC");
$unread_count = 0;
$r = $conn->query("SELECT COUNT(*) as cnt FROM contact_message WHERE is_read=0");
if ($r) $unread_count = $r->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <title>Messages - Aura Artifacts Admin</title>
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
        .top-bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px;}
        .btn{padding:9px 20px;border:none;border-radius:8px;cursor:pointer;font-size:0.85rem;font-weight:600;transition:all 0.2s;}
        .btn-gold{background:#D4AF37;color:#fff;}
        .btn-gold:hover{background:#c9a030;}
        .btn-sm{padding:6px 14px;font-size:0.78rem;}
        .btn-green{background:#2EAF7D;color:#fff;}
        .btn-green:hover{background:#259a6c;}
        .btn-red{background:#e74c3c;color:#fff;}
        .btn-red:hover{background:#c0392b;}
        .btn-blue{background:#3498db;color:#fff;}
        .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;}
        .badge-unread{background:#fff3cd;color:#856404;}
        .badge-read{background:#d4edda;color:#155724;}
        .stat-bar{display:flex;gap:16px;margin-bottom:18px;}
        .stat-item{background:#f8f9fa;padding:12px 20px;border-radius:10px;display:flex;align-items:center;gap:8px;font-size:0.9rem;}
        .stat-item strong{color:#2d3e50;font-size:1.1rem;}

        .message-card{background:#fff;border:1px solid #f0f0f0;border-radius:12px;padding:20px;margin-bottom:12px;transition:all 0.2s;}
        .message-card:hover{box-shadow:0 4px 16px rgba(0,0,0,0.08);}
        .message-card.unread{border-left:4px solid #D4AF37;background:#fffdf5;}
        .message-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;gap:12px;}
        .message-sender{font-weight:600;color:#2d3e50;font-size:0.95rem;}
        .message-email{color:#888;font-size:0.82rem;}
        .message-phone{color:#888;font-size:0.82rem;}
        .message-subject{font-weight:600;color:#2d3e50;margin-bottom:6px;font-size:0.92rem;}
        .message-body{color:#555;font-size:0.88rem;line-height:1.7;white-space:pre-wrap;background:#f8f9fa;padding:12px 16px;border-radius:8px;margin-top:8px;}
        .message-meta{display:flex;gap:12px;align-items:center;font-size:0.78rem;color:#999;margin-top:10px;}
        .message-actions{display:flex;gap:8px;margin-top:10px;}
        .message-actions form{display:inline;}
    </style>
</head>
<body>
<div class="sidebar">
    <a href="admin-dashboard" class="sidebar-logo">AURA ARTIFACTS Admin</a>
    <a href="admin-dashboard">📊 Dashboard</a>
    <a href="admin-products">📦 Products</a>
    <a href="admin-orders">📋 Orders</a>
    <a href="admin-customers">👥 Customers</a>
    <a href="admin-messages" class="active">✉️ Messages</a>
    <a href="admin-subscribers">📰 Subscribers</a>
    <a href="admin-settings">⚙️ Settings</a>
    <a href="admin-logout" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <h1 class="page-title">✉️ Contact Messages</h1>
    
    <div class="stat-bar">
        <div class="stat-item">📨 Total: <strong><?= $messages ? $messages->num_rows : 0 ?></strong></div>
        <div class="stat-item">🔴 Unread: <strong><?= $unread_count ?></strong></div>
    </div>

    <div class="card">
        <div class="top-bar">
            <h2>All Messages</h2>
            <?php if ($unread_count > 0): ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" class="btn btn-green btn-sm">✓ Mark All as Read</button>
            </form>
            <?php endif; ?>
        </div>

        <?php if ($messages && $messages->num_rows > 0): ?>
            <?php while ($m = $messages->fetch_assoc()): ?>
            <div class="message-card <?= $m['is_read'] ? '' : 'unread' ?>">
                <div class="message-header">
                    <div>
                        <div class="message-sender"><?= htmlspecialchars($m['name']) ?></div>
                        <div class="message-email">📧 <?= htmlspecialchars($m['email']) ?></div>
                        <?php if (!empty($m['phone'])): ?>
                        <div class="message-phone">📞 <?= htmlspecialchars($m['phone']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="badge <?= $m['is_read'] ? 'badge-read' : 'badge-unread' ?>">
                            <?= $m['is_read'] ? '✓ Read' : '● Unread' ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($m['subject'])): ?>
                <div class="message-subject">📌 <?= htmlspecialchars($m['subject']) ?></div>
                <?php endif; ?>

                <div class="message-body"><?= htmlspecialchars($m['message']) ?></div>

                <div class="message-meta">
                    🕐 <?= date('d M Y, H:i', strtotime($m['submitted_at'])) ?>
                </div>

                <div class="message-actions">
                    <?php if (!$m['is_read']): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="mark_read">
                        <input type="hidden" name="message_id" value="<?= $m['message_id'] ?>">
                        <button type="submit" class="btn btn-green btn-sm">✓ Mark as Read</button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" onsubmit="return confirm('Delete this message?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="message_id" value="<?= $m['message_id'] ?>">
                        <button type="submit" class="btn btn-red btn-sm">🗑 Delete</button>
                    </form>
                    <a href="mailto:<?= htmlspecialchars($m['email']) ?>" class="btn btn-blue btn-sm" style="text-decoration:none;">↩ Reply</a>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;color:#999;padding:40px;">No messages yet. Messages from the contact form will appear here.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
