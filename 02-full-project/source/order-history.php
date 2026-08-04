<?php
require_once 'config.php';
if (!isset($_SESSION['customer_id'])) { header("Location: login"); exit(); }
$cart_count  = array_sum(array_column($_SESSION['cart'] ?? [], 'quantity'));
$is_logged   = true;
$customer_id = (int)$_SESSION['customer_id'];
// Prepared statement: get customer orders
$stmt = $conn->prepare("SELECT * FROM `order` WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders = $stmt->get_result();
// Prepared statement: get custom bracelets
$stmt2 = $conn->prepare("SELECT * FROM braceletpreview WHERE customer_id = ? ORDER BY created_at DESC");
$stmt2->bind_param("i", $customer_id);
$stmt2->execute();
$customs = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aura Artifacts | order History</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&family=Lato:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    .member-bg { min-height: 85vh; display: flex; justify-content: center; align-items: center; padding: 60px 20px; background: linear-gradient(135deg, var(--secondary) 0%, var(--turquoise-light) 100%); }
    .member-card-wide { width: 100%; max-width: 620px; text-align: center; }
    .order-table { width: 100%; border-collapse: collapse; border-radius: 12px; overflow: hidden; }
    .order-table thead { background: linear-gradient(135deg, var(--primary), #1a2634); }
    .order-table th { font-family: var(--font-button); font-size: 0.7rem; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(255,255,255,0.9); padding: 14px 16px; text-align: left; font-weight: 600; }
    .order-table td { padding: 14px 16px; border-bottom: 1px solid rgba(45,62,80,0.06); font-family: var(--font-body); color: var(--text-dark); }
    .order-table tbody tr:last-child td { border-bottom: none; }
    .order-table tbody tr:hover td { background: var(--secondary); transition: var(--transition-fast); }
    .status-delivered { display: inline-block; padding: 4px 12px; background: rgba(76,175,80,0.1); color: #2e7d32; border-radius: 20px; font-size: 0.7rem; font-weight: 600; font-family: var(--font-button); text-transform: uppercase; }
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
        left: 0;
        background: white;
        border: 2px solid var(--accent);
        border-radius: 12px;
        min-width: 250px;
        max-height: 300px;
        overflow-y: auto;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        z-index: 1000;
        margin-top: 5px;
        right: auto;
    }

    .search-item {
        padding: 12px 15px;
        border-bottom: 1px solid var(--secondary);
        cursor: pointer;
        transition: var(--transition-fast);
    }

    .search-item:hover {
        background: var(--cream);
        padding-left: 20px;
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
  <div class="card member-card-wide">
    <span class="badge mb-md">🛍️ Orders</span>
    <h2 class="section-title mb-sm" style="font-size:2rem;">order History</h2>
    <p class="section-subtitle mb-md">Your Aura Artifacts journey</p>

    <table class="order-table">
      <thead>
        <tr>
          <th>order ID</th>
          <th>Date</th>
          <th>Total</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if($orders && $orders->num_rows > 0): ?>
          <?php while($order = $orders->fetch_assoc()): ?>
          <tr>
            <td><strong>#AURA-<?= $order['order_id'] ?></strong></td>
            <td><?= date('m/Y', strtotime($order['created_at'])) ?></td>
            <td>$<?= number_format($order['total_price'], 2) ?></td>
            <td><span class="status-delivered"><?= htmlspecialchars($order['status']) ?></span></td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="4">No orders found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <h2 class="section-title mb-sm mt-lg" style="font-size:2rem; margin-top: 40px;">Custom Designs</h2>
    <table class="order-table" style="margin-top: 20px;">
      <thead>
        <tr>
          <th>Design ID</th>
          <th>Date</th>
          <th>Total Price</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if($customs && $customs->num_rows > 0): ?>
          <?php while($custom = $customs->fetch_assoc()): ?>
          <tr>
            <td><strong>#CUSTOM-<?= $custom['preview_id'] ?></strong></td>
            <td><?= date('m/Y', strtotime($custom['created_at'])) ?></td>
            <td>$<?= number_format($custom['total_price'], 2) ?></td>
            <td><span class="status-delivered" style="background: rgba(33, 150, 243, 0.1); color: #1976D2;">Saved</span></td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="4">No custom designs saved.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <button class="btn-gemstone-secondary mt-md" style="width:100%;" onclick="window.location.href='profile.php'">
      ← Back to Profile
    </button>
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
