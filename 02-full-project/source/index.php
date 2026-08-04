<?php
require_once 'config.php';
$cart_count = array_sum(array_column($_SESSION['cart'] ?? [], 'quantity'));
$is_logged  = isset($_SESSION['customer_id']);

// Task 12: Recently viewed from cookie
$recently_viewed = [];
if (isset($_COOKIE['recently_viewed'])) {
    $ids = array_filter(array_map('intval', explode(',', $_COOKIE['recently_viewed'])));
    if (!empty($ids)) {
        // Prepared statement: get recently viewed products
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $stmt = $conn->prepare("SELECT product_id, name, base_price, image_url FROM product WHERE product_id IN ($placeholders)");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $rv = $stmt->get_result();
        while ($r = $rv->fetch_assoc()) $recently_viewed[] = $r;
        $stmt->close();
    }
}

// Task 12: Last order from cookie
$last_order = null;
if (isset($_COOKIE['last_order_id'])) {
    $oid = (int)$_COOKIE['last_order_id'];
    // Prepared statement: get last order from cookie
    $stmt = $conn->prepare("SELECT * FROM `order` WHERE order_id = ?");
    $stmt->bind_param("i", $oid);
    $stmt->execute();
    $ord = $stmt->get_result();
    if ($ord && $ord->num_rows > 0) $last_order = $ord->fetch_assoc();
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
    <title>Aura Artifacts | Luxury Gemstone Bracelets</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- External CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>


    <!-- Navigation -->
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

    <!-- Hero Section -->
    <section class="hero">
        <video autoplay muted loop style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;">
            <source src="https://www.pexels.com/download/video/34685468/" type="video/mp4">
        </video>
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.1); z-index: 1;"></div>
        <div class="hero-content">
            <h1 class="hero-title">Embrace Your Inner <span class="highlight-word">Light</span></h1>
            <p class="hero-subtitle">
                Discover the timeless beauty of natural gemstones, handcrafted into elegant bracelets 
                that complement your style with grace and sophistication.
            </p>
            <div class="hero-buttons">
                <a href="collections" class="btn-gemstone">Explore Collections</a>
                <a href="BraceletCustomization" class="btn-gemstone-secondary">Customize Your Bracelet</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Why Choose Aura Artifacts</h2>
                <p class="section-subtitle">
                    Every piece tells a story of nature, craftsmanship, and conscious living
                </p>
            </div>

            <div class="grid grid-3">
                <div class="feature-card fade-in">
                    <div class="feature-icon">💎</div>
                    <h3 class="feature-title">Authentic Gemstones</h3>
                    <p class="feature-description">
                        Sourced directly from ethical mines around the world. Each stone is certified 
                        for authenticity and quality.
                    </p>
                </div>

                <div class="feature-card fade-in">
                    <div class="feature-icon">🌿</div>
                    <h3 class="feature-title">Eco-Conscious</h3>
                    <p class="feature-description">
                        Sustainable practices from sourcing to packaging. We plant a tree for every 
                        bracelet sold.
                    </p>
                </div>

                <div class="feature-card fade-in">
                    <div class="feature-icon">✨</div>
                    <h3 class="feature-title">Natural Beauty</h3>
                    <p class="feature-description">
                        Each gemstone showcases unique colors and patterns from nature. 
                        Timeless elegance that never goes out of style.
                    </p>
                </div>

                <div class="feature-card fade-in">
                    <div class="feature-icon">🎨</div>
                    <h3 class="feature-title">Fully Customizable</h3>
                    <p class="feature-description">
                        Design your perfect bracelet by choosing stones, charms, and sizes that 
                        match your personal style and preferences.
                    </p>
                </div>

                <div class="feature-card fade-in">
                    <div class="feature-icon">🤲</div>
                    <h3 class="feature-title">Handcrafted</h3>
                    <p class="feature-description">
                        Every bracelet is lovingly crafted by skilled artisans who pour dedication 
                        and care into each piece.
                    </p>
                </div>

                <div class="feature-card fade-in">
                    <div class="feature-icon">🌍</div>
                    <h3 class="feature-title">Global Shipping</h3>
                    <p class="feature-description">
                        Free worldwide shipping with eco-friendly packaging. Your treasure arrives 
                        safely and sustainably.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products section" style="background: #FFFDFB;">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Featured Collections</h2>
                <p class="section-subtitle">
                    Handpicked treasures for your journey
                </p>
                <p style="font-family: var(--font-heading); font-size: 1.25rem; color: #D4AF37; margin-top: 16px; letter-spacing: 2px; font-style: italic;">Coming Soon</p>
            </div>

            <style>
                .collection-card {
                    background: white;
                    border: none;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                    display: flex;
                    flex-direction: column;
                    height: 100%;
                }

                .collection-card:hover {
                    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
                    transform: scale(1.05);
                }

                .collection-card-image {
                    width: 100%;
                    height: 300px;
                    object-fit: cover;
                    object-position: center;
                    display: block;
                }

                .collection-card-content {
                    padding: 28px 24px;
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                }

                .collection-tagline {
                    font-family: var(--font-body);
                    font-size: 0.85rem;
                    letter-spacing: 2px;
                    text-transform: uppercase;
                    color: #D4AF37;
                    margin-bottom: 12px;
                    font-weight: 500;
                }

                .collection-title {
                    font-family: var(--font-heading);
                    font-size: 1.75rem;
                    color: var(--primary);
                    margin-bottom: 16px;
                    line-height: 1.3;
                }

                .collection-description {
                    font-family: var(--font-body);
                    font-size: 0.95rem;
                    color: var(--text-light);
                    line-height: 1.6;
                    margin-bottom: 24px;
                    flex: 1;
                }

                .collection-button {
                    background: none;
                    border: none;
                    padding: 0;
                    font-family: var(--font-body);
                    font-size: 0.9rem;
                    color: var(--primary);
                    cursor: pointer;
                    position: relative;
                    display: inline-block;
                    width: fit-content;
                    font-weight: 500;
                    letter-spacing: 0.5px;
                }

                .collection-button::after {
                    content: '';
                    position: absolute;
                    bottom: -4px;
                    left: 0;
                    width: 0;
                    height: 1px;
                    background: #D4AF37;
                    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                }

                .collection-button:hover::after {
                    width: 100%;
                }

                .collection-button:hover {
                    color: #D4AF37;
                }
            </style>

            <div class="grid grid-3">
                <!-- Collection Card 1 -->
                <div class="collection-card">
                    <img src="assets/images/COLLECTION-GREEN.jpg" alt="Emerald Dreams" class="collection-card-image" style="object-position: center;">
                    <div class="collection-card-content">
                        <p class="collection-tagline">The Essence of Nature</p>
                        <h3 class="collection-title">Emerald Dreams</h3>
                        <p class="collection-description">
                            A deep dive into nature's most enchanting secrets. Discover the emerald allure that whispers stories of ancient forests and regal elegance. Crafted for those who lead with quiet power.
                        </p>
                    </div>
                </div>

                <!-- Collection Card 2 -->
                <div class="collection-card">
                    <img src="assets/images/COLLECTION-2.jpg" alt="Pearl Serenity" class="collection-card-image" style="object-position: center 90%;">
                    <div class="collection-card-content">
                        <p class="collection-tagline">Oceanic Grace</p>
                        <h3 class="collection-title">Pearl Serenity</h3>
                        <p class="collection-description">
                            Born from the heart of the ocean, polished for your most luminous moments. Timeless pearls designed to capture the light of a thousand sunsets and wrap you in effortless, feminine calm.
                        </p>
                    </div>
                </div>

                <!-- Collection Card 3 -->
                <div class="collection-card">
                    <img src="assets/images/COLLECTION-3.jpg" alt="Golden Radiance" class="collection-card-image" style="object-position: center bottom;">
                    <div class="collection-card-content">
                        <p class="collection-tagline">Eternal Glow</p>
                        <h3 class="collection-title">Golden Radiance</h3>
                        <p class="collection-description">
                            Wrapped in the warmth of the sun. Handcrafted gold pieces designed to glow with your inner light, transitioning from the soft glow of dawn to the mystery of the midnight moon.
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-xl">
                <a href="collections" class="btn-gemstone">View All Collections</a>
            </div>
        </div>
    </section>

    <!-- Divider -->
    <div class="container">
        <div class="divider"></div>
    </div>

    <!-- Testimonials -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">What Our Customers Say</h2>
                <p class="section-subtitle">
                    Stories of elegance and beauty
                </p>
            </div>

            <div class="grid grid-3">
                <div class="card text-center">
                    <p style="font-family: var(--font-heading); font-size: 1.125rem; font-style: italic; color: var(--accent); margin-bottom: 20px;">
                        "The Diamond Pendant is absolutely stunning! 
                        The brilliance and craftsmanship are exceptional. I love it!"
                    </p>
                    <h4 style="color: var(--primary); margin-bottom: 5px;">Layan Y.</h4>
                    <p style="font-size: 0.875rem; color: var(--text-light);">Saudi Arabia</p>
                </div>

                <div class="card text-center">
                    <p style="font-family: var(--font-heading); font-size: 1.125rem; font-style: italic; color: var(--accent); margin-bottom: 20px;">
                        "Exceptional quality and beautiful craftsmanship. I love that each piece 
                        is made with care and attention to detail. Highly recommend!"
                    </p>
                    <h4 style="color: var(--primary); margin-bottom: 5px;">Sara L.</h4>
                    <p style="font-size: 0.875rem; color: var(--text-light);">London, UK</p>
                </div>

                <div class="card text-center">
                    <p style="font-family: var(--font-heading); font-size: 1.125rem; font-style: italic; color: var(--accent); margin-bottom: 20px;">
                        "I customized my own bracelet and it turned out perfect! The process was 
                        easy and the result is stunning. Worth every penny."
                    </p>
                    <h4 style="color: var(--primary); margin-bottom: 5px;">Layla K.</h4>
                    <p style="font-size: 0.875rem; color: var(--text-light);">Dubai, UAE</p>
                </div>
            </div>
        </div>
    </section>

    <!-- newsletter -->
    <section class="newsletter-section" id="newsletter">
        <div class="newsletter-container">
            <h2 class="newsletter-title">Sign Up Today!</h2>
            <p class="newsletter-subtitle">
                Get News, Information and Deals Delivered to Your Inbox
            </p>
            <?php if(isset($_SESSION['newsletter_msg'])): ?>
                <div style="padding:10px; margin-bottom:15px; border-radius:5px; background:<?= $_SESSION['newsletter_type'] === 'success' ? '#d4edda' : '#f8d7da' ?>; color:<?= $_SESSION['newsletter_type'] === 'success' ? '#155724' : '#721c24' ?>;">
                    <?= htmlspecialchars($_SESSION['newsletter_msg']) ?>
                </div>
                <?php unset($_SESSION['newsletter_msg'], $_SESSION['newsletter_type']); ?>
            <?php endif; ?>
            <form class="newsletter-form" method="POST" action="actions/newsletter.php">
                <input 
                    type="email" 
                    name="email"
                    class="newsletter-input" 
                    placeholder="Enter your email address"
                    required
                >
                <button type="submit" class="newsletter-btn">Subscribe</button>
            </form>
            <p class="newsletter-note">
                We respect your privacy. Unsubscribe at any time.
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>AURA ARTIFACTS</h3>
                    <p>
                        Handcrafted gemstone jewelry that elevates your style with natural beauty. 
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
                    <p><a href="SizeGuide">Size Guide</a></p>
                    <p><a href="help">FAQ</a></p>
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
