<?php 
// ============================================================
// profile.php - customer Profile Management
// Handles profile updates, password changes, and wishlist
// Uses prepared statements for database security
// ============================================================

require_once "config.php"; 

// Redirect to login if not authenticated
if (!isset($_SESSION['customer_id'])) {
    header("Location: login");
    exit();
}
$cid = $_SESSION['customer_id'];
$success = '';

// --- Handle wishlist Removal ---
if (isset($_GET['remove_wish'])) {
    $pid = intval($_GET['remove_wish']);
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE customer_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $cid, $pid);
    $stmt->execute();
    $stmt->close();
    header("Location: profile");
    exit();
}

// --- Handle Password Change ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';
    
    // Get current password hash from DB (prepared statement)
    $stmt = $conn->prepare("SELECT password FROM customer WHERE customer_id = ?");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $pres = $stmt->get_result();
    $current_hash = $pres->fetch_assoc()['password'];
    $stmt->close();
    
    // Validate password change
    if (!password_verify($current_pass, $current_hash)) {
        $pass_error = "Current password is incorrect.";
    } elseif (strlen($new_pass) < 6) {
        $pass_error = "New password must be at least 6 characters.";
    } elseif ($new_pass !== $confirm_pass) {
        $pass_error = "New passwords do not match.";
    } else {
        // Update password with new hash (prepared statement)
        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE customer SET password = ? WHERE customer_id = ?");
        $stmt->bind_param("si", $new_hash, $cid);
        $stmt->execute();
        $stmt->close();
        $success = "Password changed successfully!";
    }

// --- Handle Profile Update ---
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Update customer profile (prepared statement)
    $stmt = $conn->prepare("UPDATE customer SET full_name = ?, phone = ?, address = ? WHERE customer_id = ?");
    $stmt->bind_param("sssi", $full_name, $phone, $address, $cid);
    $stmt->execute();
    $stmt->close();
    $_SESSION['customer_name'] = $full_name;
    $success = "Profile updated successfully!";
}

// --- Fetch customer Data ---
$stmt = $conn->prepare("SELECT * FROM customer WHERE customer_id = ?");
$stmt->bind_param("i", $cid);
$stmt->execute();
$res = $stmt->get_result();
$customer = $res->fetch_assoc();
$stmt->close();

// --- Fetch order Count ---
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM `order` WHERE customer_id = ?");
$stmt->bind_param("i", $cid);
$stmt->execute();
$ores = $stmt->get_result();
$total_orders = $ores->fetch_assoc()['cnt'];
$stmt->close();

// --- Fetch wishlist Items ---
$stmt = $conn->prepare("SELECT p.product_id, p.name, p.base_price, p.image_url FROM wishlist w JOIN product p ON w.product_id = p.product_id WHERE w.customer_id = ?");
$stmt->bind_param("i", $cid);
$stmt->execute();
$wres = $stmt->get_result();
$wishlist = [];
while ($row = $wres->fetch_assoc()) {
    $wishlist[] = $row;
}
$stmt->close();

$cart_count = array_sum(array_column($_SESSION["cart"] ?? [], "quantity")); 
$is_logged = true; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Manage your Aura Artifacts profile. View your orders, wishlist, and account settings.">
  <title>Aura Artifacts | My Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&family=Lato:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    .member-bg { min-height: 85vh; display: flex; justify-content: center; align-items: center; padding: 60px 20px; background: linear-gradient(135deg, var(--secondary) 0%, var(--turquoise-light) 100%); }
    .member-card { width: 100%; max-width: 440px; text-align: center; }
    .avatar { width: 88px; height: 88px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--turquoise)); display: flex; align-items: center; justify-content: center; font-size: 38px; margin: 0 auto 12px; box-shadow: 0 8px 24px rgba(255,158,187,0.35); }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 11px 0; border-bottom: 1px solid rgba(45,62,80,0.06); font-size: 0.9rem; }
    .info-row:last-child { border-bottom: none; }
    .info-key { font-family: var(--font-button); font-size: 0.72rem; letter-spacing: 1px; text-transform: uppercase; color: var(--text-light); font-weight: 600; }
    .info-val { font-weight: 500; color: var(--primary); }
    .btn-stack { display: flex; flex-direction: column; gap: 12px; }
    .footer-section h3 { color: var(--accent); margin-bottom: var(--space-md); font-size: 1.25rem; }
    .footer-section p, .footer-section a { color: rgba(254,254,254,0.8); font-size: 0.9375rem; line-height: 2; }
    .footer-section a:hover { color: var(--turquoise); padding-left: 8px; }
  </style>
</head>
<body>

<style>
    .gem-icon {
        transition: var(--transition-fast);
    }

    .gem-icon:hover {
        transform: scale(1.2);
        filter: drop-shadow(0 0 8px rgba(255, 158, 187, 0.6));
    }

    .favorites-dropdown {
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .favorite-item {
        display: flex;
        gap: var(--space-sm);
        padding: var(--space-sm);
        border-bottom: 1px solid var(--secondary);
        align-items: center;
    }

    .favorite-item:last-child {
        border-bottom: none;
    }

    .favorite-item-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
    }

    .favorite-item-info {
        flex: 1;
    }

    .favorite-item-name {
        font-weight: 600;
        color: var(--primary);
        font-size: 0.9rem;
    }

    .favorite-item-price {
        color: var(--accent);
        font-weight: 700;
        font-size: 0.85rem;
    }

    .favorite-item-remove {
        background: none;
        border: none;
        color: #ff6b6b;
        cursor: pointer;
        font-size: 1.2rem;
        padding: 0;
    }

    .favorite-item-remove:hover {
        transform: scale(1.2);
    }

    .empty-favorites {
        padding: var(--space-md);
        text-align: center;
        color: var(--text-light);
    }

    .nav-search {
        margin: 0 15px;
    }

    .search-input {
        padding: 8px 15px;
        border: none;
        border-bottom: 1.5px solid #D4AF37;
        border-radius: 0;
        width: 200px;
        font-size: 0.9rem;
        transition: var(--transition-fast);
        background: transparent;
        text-align: center;
        font-family: var(--font-body);
    }

    .search-input::placeholder {
        color: #B8B8B8;
        font-style: italic;
        font-family: var(--font-heading);
    }

    .search-input:focus {
        outline: none;
        border-bottom-color: var(--accent);
        box-shadow: none;
    }

    .search-results {
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: white;
        border: none;
        border-top: 1.5px solid #D4AF37;
        border-radius: 0;
        min-width: 250px;
        max-height: 300px;
        overflow-y: auto;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        z-index: 1000;
        margin-top: 8px;
        right: auto;
    }

    .search-item {
        padding: 12px 15px;
        border-bottom: 1px solid #F0F0F0;
        cursor: pointer;
        transition: var(--transition-fast);
        text-align: center;
    }

    .search-item:hover {
        background: #FFFDFB;
        padding-left: 15px;
    }

    .search-item:last-child {
        border-bottom: none;
    }

    .search-item-name {
        font-weight: 600;
        color: var(--primary);
    }

    .search-item-category {
        font-size: 0.8rem;
        color: var(--text-light);
    }

    .nav-dropdown {
        position: relative;
    }

    .nav-menu-btn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        padding: 0;
        color: var(--primary);
    }

    .menu-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border: 2px solid var(--accent);
        border-radius: 12px;
        min-width: 200px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        z-index: 1000;
        margin-top: 5px;
    }

    .menu-item {
        display: block;
        padding: 12px 20px;
        color: var(--primary);
        text-decoration: none;
        transition: var(--transition-fast);
        border-bottom: 1px solid var(--secondary);
    }

    .menu-item:hover {
        background: var(--cream);
        padding-left: 25px;
    }

    .menu-item:last-child {
        border-bottom: none;
    }

    .nav-icon {
        font-size: 1.2rem;
    }
</style>

<?php include "includes/navbar.php"; ?>

<div class="member-bg">
  <div class="card member-card">
    <div class="avatar">👤</div>
    <span class="badge mb-sm">✦ Member</span>
    <h2 class="section-title mb-sm" style="font-size:2rem;">My Profile</h2>

    <?php if($success): ?>
        <p style="color: #2e7d32; background: rgba(76,175,80,0.08); padding: 10px; border-radius: 8px; margin-bottom: 15px;"><?= $success ?></p>
    <?php endif; ?>

    <form method="POST" action="profile" style="text-align: left; margin-bottom: 20px;">
        <div style="margin-bottom: 10px;">
            <label class="info-key" style="display:block; margin-bottom:5px;">Full Name</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($customer['full_name']) ?>" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px;" required>
        </div>
        <div style="margin-bottom: 10px;">
            <label class="info-key" style="display:block; margin-bottom:5px;">Email (Read-only)</label>
            <input type="email" value="<?= htmlspecialchars($customer['email']) ?>" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; background:#f5f5f5;" readonly>
        </div>
        <div style="margin-bottom: 10px;">
            <label class="info-key" style="display:block; margin-bottom:5px;">Phone Number</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px;">
        </div>
        <div style="margin-bottom: 10px;">
            <label class="info-key" style="display:block; margin-bottom:5px;">Shipping Address</label>
            <textarea name="address" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px;" rows="3"><?= htmlspecialchars($customer['address'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn-gemstone" style="width:100%; padding:10px;">Update Profile</button>
    </form>

    <div class="divider"></div>
    <div class="info-row"><span class="info-key">Member Since</span><span class="info-val"><?= date('M Y', strtotime($customer['created_at'])) ?></span></div>
    <div class="info-row"><span class="info-key">Total Orders</span><span class="info-val text-accent" style="font-weight:700;"><?= $total_orders ?> Orders</span></div>
    <div class="divider"></div>

    <h3 style="text-align: left; margin-bottom: 15px; color: var(--primary);">My wishlist</h3>
    <?php if (empty($wishlist)): ?>
        <p style="text-align: left; color: var(--text-light); margin-bottom: 20px;">Your wishlist is empty.</p>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 15px; margin-bottom: 20px; text-align: left;">
            <?php foreach ($wishlist as $witem): ?>
                <div style="background: #fff; padding: 10px; border-radius: 8px; border: 1px solid #eee; position: relative;">
                    <a href="product-detail?id=<?= $witem['product_id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                        <img src="<?= htmlspecialchars($witem['image_url']) ?>" alt="<?= htmlspecialchars($witem['name']) ?>" style="width: 100%; height: 100px; object-fit: cover; border-radius: 6px; margin-bottom: 8px;" onerror="this.src='assets/images/placeholder.png'">
                        <div style="font-size: 0.8rem; font-weight: 600; color: var(--primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($witem['name']) ?></div>
                        <div style="font-size: 0.8rem; color: var(--accent); font-weight: 700;">$<?= number_format($witem['base_price'], 2) ?></div>
                    </a>
                    <a href="profile.php?remove_wish=<?= $witem['product_id'] ?>" 
                       onclick="return confirm('Remove from wishlist?')"
                       style="position: absolute; top: 5px; right: 8px; color: #ff6b6b; font-size: 1rem; text-decoration: none; font-weight: bold;" title="Remove">✕</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h3 style="text-align: left; margin-bottom: 15px; color: var(--primary);">🔒 Change Password</h3>
    <?php if(isset($pass_error)): ?>
        <p style="color: #c62828; background: rgba(198,40,40,0.08); padding: 10px; border-radius: 8px; margin-bottom: 15px;"><?= $pass_error ?></p>
    <?php endif; ?>
    <form method="POST" action="profile" style="text-align: left; margin-bottom: 20px;">
        <input type="hidden" name="change_password" value="1">
        <div style="margin-bottom: 10px;">
            <label class="info-key" style="display:block; margin-bottom:5px;">Current Password</label>
            <input type="password" name="current_password" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px;" required>
        </div>
        <div style="margin-bottom: 10px;">
            <label class="info-key" style="display:block; margin-bottom:5px;">New Password</label>
            <input type="password" name="new_password" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px;" required minlength="6">
        </div>
        <div style="margin-bottom: 10px;">
            <label class="info-key" style="display:block; margin-bottom:5px;">Confirm New Password</label>
            <input type="password" name="confirm_password" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px;" required>
        </div>
        <button type="submit" class="btn-gemstone" style="width:100%; padding:10px;">Change Password</button>
    </form>

    <div class="divider"></div>

    <div class="btn-stack">
      <button class="btn-gemstone" style="width:100%;" onclick="window.location.href='order-history'">View order History</button>
      <button class="btn-gemstone-secondary" style="width:100%;" onclick="window.location.href='actions/logout.php'">Logout</button>
    </div>
  </div>
</div>

<footer class="footer">
  <div class="container">
    <div class="footer-content">
      <div class="footer-section"><h3>AURA ARTIFACTS</h3><p>Handcrafted gemstone bracelets that elevate your style with natural beauty.</p></div>
      <div class="footer-section"><h3>Quick Links</h3><p><a href="index">Home</a></p><p><a href="collections">Collections</a></p><p><a href="about">About Us</a></p></div>
      <div class="footer-section"><h3>customer Care</h3><p><a href="SizeGuide">Size Guide</a></p><p><a href="help">FAQ</a></p></div>
      <div class="footer-section"><h3>Connect With Us</h3><p>Email: <a href="mailto:hello@auraartifacts.com">hello@auraartifacts.com</a></p><p>Instagram: @auraartifacts</p></div>
    </div>
    <div class="footer-bottom"><p>&copy; 2026 Aura Artifacts. All Rights Reserved.</p></div>
  </div>
</footer>

</body>
</html>

<script>
  // Search functionality
  const products = [
      { name: 'Pink Tourmaline Drop Earrings', category: 'Earrings', url: 'product-detail?id=1' },
      { name: 'Golden Radiance', category: 'Ring', url: 'product-detail?id=2' },
      { name: 'Royal Purple', category: 'Pendant', url: 'product-detail?id=3' },
      { name: 'Emerald Essence', category: 'Pendant', url: 'product-detail?id=4' },
      { name: 'Luminous Pearl', category: 'Bracelet', url: 'product-detail?id=5' },
      { name: 'Sapphire Serenity', category: 'Ring', url: 'product-detail?id=4' },
      { name: 'Passionate Fire', category: 'Anklet', url: 'product-detail?id=5' },
      { name: 'Timeless Brilliance', category: 'Pendant', url: 'product-detail?id=3' },
      { name: 'Crystal Radiance', category: 'Sunglasses', url: 'product-detail?id=6' }
  ];

  function searchProducts() {
      const input = document.getElementById('searchInput').value.toLowerCase();
      const resultsDiv = document.getElementById('searchResults');
      
      if (input.length === 0) {
          resultsDiv.style.display = 'none';
          return;
      }
      
      const filtered = products.filter(p => 
          p.name.toLowerCase().includes(input) || 
          p.category.toLowerCase().includes(input)
      );
      
      if (filtered.length === 0) {
          resultsDiv.innerHTML = '<div style="padding: 15px; text-align: center; color: var(--text-light);">No products found</div>';
      } else {
          resultsDiv.innerHTML = filtered.map(p => 
              `<a href="${p.url}" class="search-item" style="text-decoration: none; color: inherit;">
                  <div class="search-item-name">${p.name}</div>
                  <div class="search-item-category">${p.category}</div>
              </a>`
          ).join('');
      }
      
      resultsDiv.style.display = 'block';
  }

  // Close menus when clicking outside
  document.addEventListener('click', function(event) {
      const searchResults = document.getElementById('searchResults');
      const menuDropdown = document.getElementById('menuDropdown');
      const searchInput = document.getElementById('searchInput');
      const menuBtn = document.querySelector('.nav-menu-btn');
      
      if (searchInput && !searchInput.contains(event.target) && searchResults && !searchResults.contains(event.target)) {
          searchResults.style.display = 'none';
      }
      
      if (menuBtn && !menuBtn.contains(event.target) && menuDropdown && !menuDropdown.contains(event.target)) {
          menuDropdown.style.display = 'none';
      }
  });
</script>
