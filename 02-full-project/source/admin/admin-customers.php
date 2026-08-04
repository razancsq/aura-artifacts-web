<?php
// ============================================================
// admin-customers.php - customer Management
// Features: View, Edit, Create, Change Password, Promote to Admin
// ============================================================
require_once '../config.php';
require_once 'admin-auth.php';

$msg = '';
$error = '';

// ------- CREATE CUSTOMER -------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        $error = "Name, email, and password are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT customer_id FROM customer WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "A customer with this email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO customer (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $hash, $phone, $address);
            $stmt->execute();
            $msg = "customer '$name' created successfully!";
        }
        $stmt->close();
    }
}

// ------- EDIT CUSTOMER -------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $cid = (int)$_POST['customer_id'];
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!$name || !$email) {
        $error = "Name and email are required.";
    } else {
        // Check email uniqueness
        $stmt = $conn->prepare("SELECT customer_id FROM customer WHERE email=? AND customer_id!=?");
        $stmt->bind_param("si", $email, $cid);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Another customer already has this email.";
        } else {
            $stmt = $conn->prepare("UPDATE customer SET full_name=?, email=?, phone=?, address=? WHERE customer_id=?");
            $stmt->bind_param("ssssi", $name, $email, $phone, $address, $cid);
            $stmt->execute();
            $msg = "customer updated successfully!";
        }
        $stmt->close();
    }
}

// ------- CHANGE CUSTOMER PASSWORD -------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    $cid = (int)$_POST['customer_id'];
    $new_pass = $_POST['new_password'] ?? '';
    if (strlen($new_pass) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE customer SET password=? WHERE customer_id=?");
        $stmt->bind_param("si", $hash, $cid);
        $stmt->execute();
        $stmt->close();
        $msg = "customer password updated successfully!";
    }
}

// ------- PROMOTE TO ADMIN -------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'promote') {
    $cid = (int)$_POST['customer_id'];
    // Get customer data
    $stmt = $conn->prepare("SELECT * FROM customer WHERE customer_id=?");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $cust = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($cust) {
        // Check if admin with same email exists
        $stmt = $conn->prepare("SELECT admin_id FROM admin WHERE email=?");
        $stmt->bind_param("s", $cust['email']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "An admin with this email already exists.";
        } else {
            // Create admin account with same credentials
            $username = strtolower(str_replace(' ', '_', $cust['full_name']));
            $stmt = $conn->prepare("INSERT INTO admin (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $cust['email'], $cust['password']);
            $stmt->execute();
            $msg = htmlspecialchars($cust['full_name']) . " has been promoted to Admin!";
        }
        $stmt->close();
    }
}

// ------- DEMOTE FROM ADMIN (Make User) -------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'demote') {
    $cid = (int)$_POST['customer_id'];
    // Get customer data
    $stmt = $conn->prepare("SELECT * FROM customer WHERE customer_id=?");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $cust = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($cust) {
        // Prevent demoting yourself
        $stmt = $conn->prepare("SELECT admin_id FROM admin WHERE email=?");
        $stmt->bind_param("s", $cust['email']);
        $stmt->execute();
        $admin_row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($admin_row && $admin_row['admin_id'] == $_SESSION['admin_id']) {
            $error = "You cannot demote yourself from admin.";
        } elseif ($admin_row) {
            $stmt = $conn->prepare("DELETE FROM admin WHERE email=?");
            $stmt->bind_param("s", $cust['email']);
            $stmt->execute();
            $stmt->close();
            $msg = htmlspecialchars($cust['full_name']) . " has been demoted to regular user.";
        } else {
            $error = "This customer is not an admin.";
        }
    }
}

// ------- DELETE CUSTOMER -------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $cid = (int)$_POST['customer_id'];
    $stmt = $conn->prepare("DELETE FROM customer WHERE customer_id=?");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $stmt->close();
    $msg = "customer deleted.";
}

// ------- FETCH CUSTOMERS -------
$search = trim($_GET['search'] ?? '');
if ($search) {
    $searchParam = "%$search%";
    $stmt = $conn->prepare("SELECT c.*, (SELECT COUNT(*) FROM `order` o WHERE o.customer_id=c.customer_id) AS orders FROM customer c WHERE c.full_name LIKE ? OR c.email LIKE ? ORDER BY c.created_at DESC");
    $stmt->bind_param("ss", $searchParam, $searchParam);
    $stmt->execute();
    $customers = $stmt->get_result();
} else {
    $customers = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM `order` o WHERE o.customer_id=c.customer_id) AS orders FROM customer c ORDER BY c.created_at DESC");
}

// Check which customers are already admins
$admin_emails = [];
$r = $conn->query("SELECT email FROM admin");
if ($r) { while ($row = $r->fetch_assoc()) $admin_emails[] = $row['email']; }

$total_customers = 0;
$r = $conn->query("SELECT COUNT(*) as cnt FROM customer");
if ($r) $total_customers = $r->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <title>Customers - Aura Artifacts Admin</title>
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
        td{padding:12px 14px;border-bottom:1px solid #f0f0f0;font-size:0.88rem;vertical-align:middle;}
        tr:last-child td{border-bottom:none;}
        tr:hover td{background:#fafafa;}
        .search-row{display:flex;gap:12px;margin-bottom:18px;}
        .search-row input{flex:1;padding:9px 14px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:0.88rem;}
        .btn{padding:9px 20px;border:none;border-radius:8px;cursor:pointer;font-size:0.85rem;font-weight:600;transition:all 0.2s;}
        .btn-sm{padding:6px 14px;font-size:0.78rem;}
        .btn-gold{background:#D4AF37;color:#fff;}
        .btn-gold:hover{background:#c9a030;transform:translateY(-1px);}
        .btn-blue{background:#3498db;color:#fff;}
        .btn-blue:hover{background:#2980b9;}
        .btn-green{background:#2EAF7D;color:#fff;}
        .btn-green:hover{background:#259a6c;}
        .btn-red{background:#e74c3c;color:#fff;}
        .btn-red:hover{background:#c0392b;}
        .btn-purple{background:#8e44ad;color:#fff;}
        .btn-purple:hover{background:#7d3c98;}
        .btn-orange{background:#e67e22;color:#fff;}
        .btn-orange:hover{background:#d35400;}
        .avatar{width:36px;height:36px;border-radius:50%;background:#2d3e50;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:700;}
        .alert{padding:14px 18px;border-radius:10px;margin-bottom:18px;font-size:0.9rem;}
        .alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
        .alert-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
        .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:600;}
        .badge-admin{background:#e8daef;color:#6c3483;}
        .stat-bar{display:flex;gap:16px;margin-bottom:18px;}
        .stat-item{background:#f8f9fa;padding:12px 20px;border-radius:10px;display:flex;align-items:center;gap:8px;font-size:0.9rem;}
        .stat-item strong{color:#2d3e50;font-size:1.1rem;}
        .top-bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px;}
        .actions-cell{display:flex;gap:6px;flex-wrap:wrap;}

        /* Form Grid */
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
        .form-group{margin-bottom:0;}
        .form-group.full{grid-column:1/-1;}
        .form-group label{display:block;font-size:0.78rem;text-transform:uppercase;letter-spacing:1px;color:#666;font-weight:600;margin-bottom:6px;}
        .form-group input{width:100%;padding:10px 14px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:0.88rem;font-family:'Poppins',sans-serif;}
        .form-group input:focus{outline:none;border-color:#ff9ebb;box-shadow:0 0 0 3px rgba(255,158,187,0.12);}

        /* Modal */
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;}
        .modal-overlay.active{display:flex;}
        .modal-box{background:#fff;border-radius:16px;padding:28px;width:100%;max-width:500px;position:relative;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:slideUp 0.25s ease;}
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
    <a href="admin-customers" class="active">👥 Customers</a>
    <a href="admin-messages">✉️ Messages</a>
    <a href="admin-subscribers">📰 Subscribers</a>
    <a href="admin-settings">⚙️ Settings</a>
    <a href="admin-logout" class="logout">🚪 Logout</a>
</div>

<div class="main">
    <h1 class="page-title">👥 Customers</h1>

    <?php if ($msg): ?><div class="alert alert-success">✅ <?= $msg ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error">❌ <?= $error ?></div><?php endif; ?>

    <div class="stat-bar">
        <div class="stat-item">👥 Total: <strong><?= $total_customers ?></strong></div>
    </div>

    <!-- Create New customer -->
    <div class="card">
        <h2>➕ Create New customer</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required placeholder="john@example.com">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" placeholder="+966 50 123 4567">
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required minlength="6" placeholder="Min 6 characters">
                </div>
                <div class="form-group full">
                    <label>Address</label>
                    <input type="text" name="address" placeholder="123 Main St, City">
                </div>
            </div>
            <button type="submit" class="btn btn-gold" style="margin-top:14px;">➕ Create customer</button>
        </form>
    </div>

    <!-- customer List -->
    <div class="card">
        <div class="top-bar">
            <h2>All Customers</h2>
        </div>
        <form method="GET" class="search-row">
            <input type="text" name="search" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-gold">Search</button>
            <?php if ($search): ?><a href="admin-customers" class="btn btn-blue" style="text-decoration:none;">Clear</a><?php endif; ?>
        </form>
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($c = $customers->fetch_assoc()): ?>
            <tr>
                <td><div class="avatar"><?= strtoupper(substr($c['full_name'],0,1)) ?></div></td>
                <td>
                    <?= htmlspecialchars($c['full_name']) ?>
                    <?php if (in_array($c['email'], $admin_emails)): ?>
                        <span class="badge badge-admin">Admin</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($c['email']) ?></td>
                <td><?= htmlspecialchars($c['phone'] ?? '-') ?></td>
                <td><?= $c['orders'] ?></td>
                <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                <td>
                    <div class="actions-cell">
                        <button type="button" class="btn btn-blue btn-sm"
                            onclick="openEditModal(<?= $c['customer_id'] ?>, '<?= htmlspecialchars(addslashes($c['full_name']), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($c['email']), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($c['phone'] ?? ''), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($c['address'] ?? ''), ENT_QUOTES) ?>')">
                            ✏️ Edit
                        </button>
                        <button type="button" class="btn btn-green btn-sm"
                            onclick="openPasswordModal(<?= $c['customer_id'] ?>, '<?= htmlspecialchars(addslashes($c['full_name']), ENT_QUOTES) ?>')">
                            🔑 Password
                        </button>
                        <?php if (!in_array($c['email'], $admin_emails)): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Promote <?= htmlspecialchars(addslashes($c['full_name'])) ?> to Admin? They will have full admin access.');">
                            <input type="hidden" name="action" value="promote">
                            <input type="hidden" name="customer_id" value="<?= $c['customer_id'] ?>">
                            <button type="submit" class="btn btn-purple btn-sm">👑 Make Admin</button>
                        </form>
                        <?php else: ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Demote <?= htmlspecialchars(addslashes($c['full_name'])) ?> to regular user? Their admin access will be revoked.');">
                            <input type="hidden" name="action" value="demote">
                            <input type="hidden" name="customer_id" value="<?= $c['customer_id'] ?>">
                            <button type="submit" class="btn btn-orange btn-sm">👤 Make User</button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this customer? This cannot be undone.');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="customer_id" value="<?= $c['customer_id'] ?>">
                            <button type="submit" class="btn btn-red btn-sm">🗑</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-overlay">
    <div class="modal-box">
        <button class="modal-close" onclick="closeEditModal()">✕</button>
        <h2 class="modal-title">✏️ Edit customer</h2>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="customer_id" id="edit-id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" id="edit-name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit-email" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" id="edit-phone">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" id="edit-address">
                </div>
            </div>
            <button type="submit" class="btn btn-gold" style="margin-top:14px;">💾 Save Changes</button>
        </form>
    </div>
</div>

<!-- Password Modal -->
<div id="passwordModal" class="modal-overlay">
    <div class="modal-box">
        <button class="modal-close" onclick="closePasswordModal()">✕</button>
        <h2 class="modal-title">🔑 Change Password</h2>
        <p style="font-size:0.88rem;color:#666;margin-bottom:14px;">Changing password for: <strong id="pw-name"></strong></p>
        <form method="POST">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="customer_id" id="pw-id">
            <div class="form-group" style="margin-bottom:14px;">
                <label>New Password</label>
                <input type="password" name="new_password" required minlength="6" placeholder="Min 6 characters">
            </div>
            <button type="submit" class="btn btn-gold">🔑 Update Password</button>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name, email, phone, address) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-phone').value = phone;
    document.getElementById('edit-address').value = address;
    document.getElementById('editModal').classList.add('active');
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

function openPasswordModal(id, name) {
    document.getElementById('pw-id').value = id;
    document.getElementById('pw-name').textContent = name;
    document.getElementById('passwordModal').classList.add('active');
}
function closePasswordModal() {
    document.getElementById('passwordModal').classList.remove('active');
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
});
</script>
</body>
</html>
