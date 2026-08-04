<?php
// ============================================================
// admin-settings.php - Admin Settings & Password Reset Management
// ============================================================
require_once '../config.php';
require_once 'admin-auth.php';

$admin_id = (int)$_SESSION['admin_id'];
$admin_name = $_SESSION['admin_username'] ?? 'Admin';
$success = '';
$error = '';

// Get admin info
$stmt = $conn->prepare("SELECT * FROM admin WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$res = $stmt->get_result();
$admin = $res->fetch_assoc();

// ------- CHANGE ADMIN PASSWORD -------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'change_admin_password') {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (!password_verify($current_pass, $admin['password'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new_pass) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "New passwords do not match.";
    } else {
        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admin SET password=? WHERE admin_id=?");
        $stmt->bind_param("si", $new_hash, $admin_id);
        $stmt->execute();
        $stmt->close();
        $success = "Your password updated successfully!";
        $stmt = $conn->prepare("SELECT * FROM admin WHERE admin_id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $admin = $res->fetch_assoc();
    }
}

// ------- RESET USER PASSWORD (from password reset request) -------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'reset_user_password') {
    $request_id = (int)$_POST['request_id'];
    $user_email = $_POST['user_email'] ?? '';
    $user_type = $_POST['user_type'] ?? 'customer';
    $new_pass = $_POST['new_password'] ?? '';

    if (strlen($new_pass) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);

        if ($user_type === 'admin') {
            $stmt = $conn->prepare("UPDATE admin SET password=? WHERE email=?");
        } else {
            $stmt = $conn->prepare("UPDATE customer SET password=? WHERE email=?");
        }
        $stmt->bind_param("ss", $hash, $user_email);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected > 0) {
            // Mark request as completed
            $stmt = $conn->prepare("UPDATE password_reset_request SET status='completed', completed_at=NOW() WHERE request_id=?");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $stmt->close();
            $success = "Password reset for $user_email completed!";
        } else {
            $error = "User not found with email: $user_email";
        }
    }
}

// ------- MARK REQUEST AS COMPLETED (without changing password) -------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'mark_completed') {
    $request_id = (int)$_POST['request_id'];
    $stmt = $conn->prepare("UPDATE password_reset_request SET status='completed', completed_at=NOW() WHERE request_id=?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt->close();
    $success = "Request marked as completed.";
}

// ------- MARK REQUEST AS EXPIRED -------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'mark_expired') {
    $request_id = (int)$_POST['request_id'];
    $stmt = $conn->prepare("UPDATE password_reset_request SET status='expired' WHERE request_id=?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt->close();
    $success = "Request marked as expired.";
}

// Fetch password reset requests
$resets = $conn->query("SELECT * FROM password_reset_request ORDER BY requested_at DESC LIMIT 20");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <title>Settings - Aura Artifacts Admin</title>
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
        .form-group{margin-bottom:16px;}
        .form-group label{display:block;font-size:0.78rem;text-transform:uppercase;letter-spacing:1px;color:#666;font-weight:600;margin-bottom:6px;}
        .form-group input{width:100%;padding:11px 14px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:0.9rem;font-family:'Poppins',sans-serif;transition:border-color 0.2s;}
        .form-group input:focus{outline:none;border-color:#ff9ebb;box-shadow:0 0 0 3px rgba(255,158,187,0.12);}
        .form-group input[readonly]{background:#f5f5f5;cursor:not-allowed;}
        .btn{padding:11px 24px;border:none;border-radius:8px;cursor:pointer;font-size:0.88rem;font-weight:600;transition:all 0.2s;}
        .btn-sm{padding:6px 14px;font-size:0.78rem;}
        .btn-gold{background:#D4AF37;color:#fff;}
        .btn-gold:hover{background:#c9a030;transform:translateY(-1px);box-shadow:0 4px 12px rgba(212,175,55,0.3);}
        .btn-green{background:#2EAF7D;color:#fff;}
        .btn-green:hover{background:#259a6c;}
        .btn-red{background:#e74c3c;color:#fff;}
        .btn-red:hover{background:#c0392b;}
        .btn-blue{background:#3498db;color:#fff;}
        .btn-blue:hover{background:#2980b9;}
        .alert{padding:14px 18px;border-radius:10px;margin-bottom:18px;font-size:0.9rem;}
        .alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
        .alert-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
        .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;}
        .info-item .label{font-size:0.72rem;text-transform:uppercase;letter-spacing:1px;color:#888;font-weight:600;}
        .info-item .value{font-size:0.95rem;color:#2d3e50;margin-top:4px;}
        table{width:100%;border-collapse:collapse;}
        th{background:#f0f2f5;padding:10px 14px;text-align:left;font-size:0.75rem;text-transform:uppercase;letter-spacing:1px;color:#666;}
        td{padding:12px 14px;border-bottom:1px solid #f0f0f0;font-size:0.88rem;vertical-align:middle;}
        tr:last-child td{border-bottom:none;}
        tr:hover td{background:#fafafa;}
        .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;}
        .badge-pending{background:#fff3cd;color:#856404;}
        .badge-completed{background:#d4edda;color:#155724;}
        .badge-expired{background:#f8d7da;color:#721c24;}
        .badge-customer{background:#cce5ff;color:#004085;}
        .badge-admin{background:#e8daef;color:#6c3483;}
        .actions-cell{display:flex;gap:6px;flex-wrap:wrap;align-items:center;}

        /* Modal */
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;}
        .modal-overlay.active{display:flex;}
        .modal-box{background:#fff;border-radius:16px;padding:28px;width:100%;max-width:420px;position:relative;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:slideUp 0.25s ease;}
        @keyframes slideUp{from{transform:translateY(16px);opacity:0;}to{transform:translateY(0);opacity:1;}}
        .modal-close{position:absolute;top:12px;right:16px;background:none;border:none;font-size:1.3rem;cursor:pointer;color:#999;}
        .modal-close:hover{color:#333;}
        .modal-title{font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:#2d3e50;margin-bottom:18px;}
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
    <a href="admin-settings" class="active">⚙️ Settings</a>
    <a href="admin-logout" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <h1 class="page-title">⚙️ Settings</h1>

    <?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- Admin Info -->
    <div class="card">
        <h2>👤 Admin Account Info</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="label">Username</div>
                <div class="value"><?= htmlspecialchars($admin['username']) ?></div>
            </div>
            <div class="info-item">
                <div class="label">Email</div>
                <div class="value"><?= htmlspecialchars($admin['email']) ?></div>
            </div>
            <div class="info-item">
                <div class="label">Account Created</div>
                <div class="value"><?= date('d M Y, H:i', strtotime($admin['created_at'])) ?></div>
            </div>
            <div class="info-item">
                <div class="label">Admin ID</div>
                <div class="value">#<?= $admin['admin_id'] ?></div>
            </div>
        </div>
    </div>

    <!-- Change Password -->
    <div class="card">
        <h2>🔒 Change My Password</h2>
        <form method="POST">
            <input type="hidden" name="form_action" value="change_admin_password">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required placeholder="Enter current password">
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required minlength="6" placeholder="At least 6 characters">
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required placeholder="Re-enter new password">
            </div>
            <button type="submit" class="btn btn-gold">Update Password</button>
        </form>
    </div>

    <!-- Password Reset Requests -->
    <div class="card">
        <h2>📋 Password Reset Requests</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Requested At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resets && $resets->num_rows > 0): ?>
                    <?php while ($r = $resets->fetch_assoc()): ?>
                    <tr>
                        <td><?= $r['request_id'] ?></td>
                        <td><?= htmlspecialchars($r['email']) ?></td>
                        <td><span class="badge badge-<?= $r['user_type'] ?>"><?= ucfirst($r['user_type']) ?></span></td>
                        <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                        <td><?= date('d M Y, H:i', strtotime($r['requested_at'])) ?></td>
                        <td>
                            <div class="actions-cell">
                                <?php if ($r['status'] === 'pending'): ?>
                                <button type="button" class="btn btn-blue btn-sm" 
                                    onclick="openResetModal(<?= $r['request_id'] ?>, '<?= htmlspecialchars(addslashes($r['email']), ENT_QUOTES) ?>', '<?= $r['user_type'] ?>')">
                                    🔑 Reset Password
                                </button>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="form_action" value="mark_completed">
                                    <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                                    <button type="submit" class="btn btn-green btn-sm">✓ Complete</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="form_action" value="mark_expired">
                                    <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                                    <button type="submit" class="btn btn-red btn-sm">✕ Expire</button>
                                </form>
                                <?php else: ?>
                                <span style="color:#999;font-size:0.82rem;">
                                    <?= $r['status'] === 'completed' ? '✓ Done' : '✕ Expired' ?>
                                    <?php if ($r['completed_at']): ?>
                                     - <?= date('d M Y', strtotime($r['completed_at'])) ?>
                                    <?php endif; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center;color:#999;padding:24px;">No reset requests yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="resetModal" class="modal-overlay">
    <div class="modal-box">
        <button class="modal-close" onclick="document.getElementById('resetModal').classList.remove('active')">✕</button>
        <h2 class="modal-title">🔑 Reset User Password</h2>
        <p style="font-size:0.88rem;color:#666;margin-bottom:14px;">
            Resetting password for: <strong id="reset-email-display"></strong>
        </p>
        <form method="POST">
            <input type="hidden" name="form_action" value="reset_user_password">
            <input type="hidden" name="request_id" id="reset-request-id">
            <input type="hidden" name="user_email" id="reset-email">
            <input type="hidden" name="user_type" id="reset-type">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required minlength="6" placeholder="Min 6 characters">
            </div>
            <button type="submit" class="btn btn-gold">🔑 Reset Password & Complete</button>
        </form>
    </div>
</div>

<script>
function openResetModal(id, email, type) {
    document.getElementById('reset-request-id').value = id;
    document.getElementById('reset-email').value = email;
    document.getElementById('reset-type').value = type;
    document.getElementById('reset-email-display').textContent = email + ' (' + type + ')';
    document.getElementById('resetModal').classList.add('active');
}

document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
});
</script>
</body>
</html>
