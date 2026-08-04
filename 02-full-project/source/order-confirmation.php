<?php
require_once 'config.php';
$cart_count = array_sum(array_column($_SESSION['cart'] ?? [], 'quantity'));
$is_logged  = isset($_SESSION['customer_id']);
$order_id   = (int)($_GET['order_id'] ?? 0);
$order = null;
if ($order_id && $is_logged) {
    $cid = (int)$_SESSION['customer_id'];
    // Prepared statement: get order details
    $stmt = $conn->prepare("SELECT * FROM `order` WHERE order_id = ? AND customer_id = ?");
    $stmt->bind_param("ii", $order_id, $cid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) $order = $res->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aura Artifacts - Discover the beauty of natural gemstone bracelets. Handcrafted with love and sustainable practices.">
    <meta name="keywords" content="gemstone bracelets, natural stones, luxury jewelry, sustainable jewelry, handcrafted jewelry">
    <title>Aura Artifacts | Confirmation</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- External CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

    <!-- ═══════════════════════════════════════════════════════════
        NAVIGATION
        ═══════════════════════════════════════════════════════════ -->
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

    <!-- Main Content -->
    <main class="cart-container">
        <div class="confirmation-box">
            <div class="success-icon">✓</div>
            <h1>Thank You for Your order!</h1>
            <p style="color: var(--text-light); margin-bottom: var(--space-md);">
                Your order has been successfully placed. We have sent a confirmation email to your inbox.
            </p>
            
            <div class="order-details">
                <?php if ($order): ?>
                <div class="summary-row">
                    <span><strong>order Number:</strong></span>
                    <span>#AURA-<?= htmlspecialchars($order['order_id']) ?></span>
                </div>
                <div class="summary-row">
                    <span><strong>Date:</strong></span>
                    <span><?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                </div>
                <div class="summary-row">
                    <span><strong>Total Paid:</strong></span>
                    <span>$<?= number_format($order['total_price'], 2) ?></span>
                </div>
                <div style="margin-top: var(--space-sm); font-size: 0.9rem;">
                    <strong>Shipping To:</strong><br>
                    <?= htmlspecialchars($_SESSION['customer_name'] ?? 'customer') ?><br>
                    <?= htmlspecialchars($order['address']) ?>
                </div>
                <?php else: ?>
                <p>order details could not be found. Please check your order history.</p>
                <?php endif; ?>
            </div>

            <h3>What's Next?</h3>
            <p style="font-size: 0.95rem; margin-bottom: var(--space-md);">
                Estimated Delivery: <strong>3-5 Business Days</strong><br>
                You will receive a tracking number via email soon.
            </p>

            <div style="margin-top: var(--space-lg);">
                <a href="collections" class="btn btn-primary">Continue Shopping</a>
            </div>
            
            <div style="margin-top: var(--space-md); font-size: 0.85rem; color: var(--text-light);">
                <p>💎 <strong>Caring for your Jewelry:</strong> Avoid contact with water and perfumes to keep your pieces shining.</p>
            </div>
        </div>
    </main>

        
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>AURA ARTIFACTS</h3>
                    <p>
                        Handcrafted gemstone bracelets that elevate your style with natural beauty.
                        Made with love, care, and sustainable practices.
                    </p>
                </div>
    
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <p><a href="index">Home</a></p>
                    <p><a href="collections">Collections</a></p>
                    <p><a href="about">About Us</a></p>
                    <p><a href="#contact">Contact</a></p>
                    <p><a href="admin/admin-login">Admin Portal</a></p>
                </div>
    
                <div class="footer-section">
                    <h3>customer Care</h3>
                    <p><a href="#">Shipping & Returns</a></p>
                    <p><a href="#">Size Guide</a></p>
                    <p><a href="#">Care Instructions</a></p>
                    <p><a href="#">FAQ</a></p>
                </div>
    
                <div class="footer-section">
                    <h3>Connect With Us</h3>
                    <p>Email: <a href="mailto:info@auraartifacts.com">info@auraartifacts.com</a></p>
                    <p>Phone: +966 50 123 4567</p>
                    <p>Instagram: @auraartifacts</p>
                    <p>Follow us for daily inspiration ✨</p>
                </div>
            </div>
    
            <div class="footer-bottom">
                <p>&copy; 2026 Aura Artifacts. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

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

</body>
</html>
