<?php
require_once "config.php";
$cart_count = array_sum(array_column($_SESSION["cart"] ?? [], "quantity"));
$is_logged = isset($_SESSION["customer_id"]);

// Fetch products
// Fetch products (ordered to match original project layout)
$products_res = $conn->query("SELECT p.*, c.name as category_name FROM product p LEFT JOIN category c ON p.category_id = c.category_id ORDER BY FIELD(p.name, 'Pink Tourmaline Drop Earrings','Golden Radiance','Royal Purple','Emerald Essence','Luminous Pearl','Sapphire Serenity','Passionate Fire','Timeless Brilliance','Crystal Radiance')");
$products = [];
if ($products_res) {
    while ($row = $products_res->fetch_assoc()) {
        $products[] = $row;
    }
}

// category filter order (matches original project layout)
$categories = ['Bracelets', 'Rings', 'Pendants', 'Earrings', 'Anklets', 'Sunglasses'];
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse our exquisite collection of handcrafted gemstone bracelets. Each piece is unique and carries healing energy.">
    <title>Collections | Aura Artifacts</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/pages.css">
    <link rel="stylesheet" href="assets/css/collections.css">
</head>
<body>

    <?php include "includes/navbar.php"; ?>

    <!-- Collections Header -->
    <section class="collections-header">
        <div class="container text-center" style="padding: 40px 0;">
            <h1 style="font-size: 3rem; margin-bottom: 20px; font-family: var(--font-heading);">Our Gemstone Collections</h1>
            <p style="font-size: 1.2rem;">
                Discover bracelets crafted with intention, infused with healing energy, 
                and designed to complement your unique journey.
            </p>
        </div>
    </section>

    <!-- Gemstone Discovery Quiz -->
    <section class="gemstone-discovery">
        <div class="container">
            <div class="discovery-header text-center">
                <h2>Discover Your Perfect Gemstone</h2>
                <p>Explore the natural beauty and unique colors of our gemstones</p>
            </div>

            <div class="discovery-content">
                <div class="quiz-box">
                    <!-- Initial State -->
                    <div id="quiz-start" class="quiz-start">
                        <h3 class="quiz-title">Find Your Perfect Gemstone</h3>
                        <p class="quiz-subtitle">
                            What's your mood today? Discover which gemstone matches your energy
                        </p>
                        <button class="quiz-start-btn" onclick="document.getElementById('quiz-start').style.display='none'; document.getElementById('quiz-questions').style.display='block';">
                            Start Quiz
                        </button>
                    </div>

                    <!-- Quiz Questions -->
                    <div id="quiz-questions" class="quiz-questions" style="display: none;">
                        <h3 class="quiz-title">What's Your Mood?</h3>
                        <p class="quiz-subtitle">Choose the mood that matches how you feel right now</p>

                        <input type="radio" id="happy" name="mood" value="happy" onchange="showResult('happy')" style="display: none;">
                        <input type="radio" id="excited" name="mood" value="excited" onchange="showResult('excited')" style="display: none;">
                        <input type="radio" id="surprised" name="mood" value="surprised" onchange="showResult('surprised')" style="display: none;">
                        <input type="radio" id="anxious" name="mood" value="anxious" onchange="showResult('anxious')" style="display: none;">
                        <input type="radio" id="calm" name="mood" value="calm" onchange="showResult('calm')" style="display: none;">
                        <input type="radio" id="confident" name="mood" value="confident" onchange="showResult('confident')" style="display: none;">
                        <input type="radio" id="passionate" name="mood" value="passionate" onchange="showResult('passionate')" style="display: none;">
                        <input type="radio" id="elegant" name="mood" value="elegant" onchange="showResult('elegant')" style="display: none;">

                        <div class="quiz-options">
                            <div class="quiz-option"><label for="happy"><span>Happy</span></label></div>
                            <div class="quiz-option"><label for="excited"><span>Excited</span></label></div>
                            <div class="quiz-option"><label for="surprised"><span>Surprised</span></label></div>
                            <div class="quiz-option"><label for="anxious"><span>Anxious</span></label></div>
                            <div class="quiz-option"><label for="calm"><span>Calm</span></label></div>
                            <div class="quiz-option"><label for="confident"><span>Confident</span></label></div>
                            <div class="quiz-option"><label for="passionate"><span>Passionate</span></label></div>
                            <div class="quiz-option"><label for="elegant"><span>Elegant</span></label></div>
                        </div>

                        <div class="quiz-results" id="quiz-results-container" style="display: none;">
                            <p class="quiz-match-label">Perfect Match!</p>
                            <h4 id="result-name">Rose Quartz</h4>
                            <p id="result-desc"></p>
                            <a href="#" class="result-btn btn-gemstone" style="display: inline-block; margin-top: 15px;">Shop Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- category Filter -->
    <section class="filter-section">
        <div class="filter-container">
            <button class="filter-btn active" onclick="filterProducts('all')">All</button>
            <?php foreach($categories as $cat): ?>
            <button class="filter-btn" onclick="filterProducts('<?= htmlspecialchars($cat) ?>')"><?= htmlspecialchars($cat) ?></button>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Products Grid -->
    <section class="products-grid section">
        <div class="container">
            <div class="grid grid-3">
                <?php foreach($products as $product): ?>
                <div class="product-card card fade-in" data-category="<?= htmlspecialchars($product['category_name']) ?>">
                    <a href="product-detail?id=<?= $product['product_id'] ?>" style="text-decoration: none; color: inherit;">
                        <div class="card-image-wrapper">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="card-image" onerror="this.src='assets/images/placeholder.png';">
                            <?php
                            // Badges matching old project
                            $badges = [
                                'Pink Tourmaline Drop Earrings' => 'Bestseller',
                                'Golden Radiance' => 'New Arrival',
                                'Royal Purple' => 'Limited Edition',
                                'Timeless Brilliance' => 'Staff Pick'
                            ];
                            if (isset($badges[$product['name']])): ?>
                            <span class="product-badge"><?= $badges[$product['name']] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-info">
                            <span class="badge mb-sm"><?= htmlspecialchars($product['category_name']) ?></span>
                            <h3 class="card-title"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="card-description">
                                <?= htmlspecialchars(substr($product['description'], 0, 120)) ?>...
                            </p>
                            <div class="gemstone-properties" style="margin: 10px 0;">
                                <span class="property-tag"><?= htmlspecialchars($product['category_name']) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                                <div>
                                    <span style="font-size: 0.8rem; color: var(--text-light); text-transform: uppercase; letter-spacing: 1px;">Price</span>
                                    <span class="card-price" style="display: block;">$<?= number_format($product['base_price'], 2) ?></span>
                                </div>
                                <button class="btn-gemstone" style="padding: 10px 20px; font-size: 0.8rem;">View Details</button>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Custom Bracelet CTA -->
    <section class="section section-alt">
        <div class="container text-center">
            <h2 class="section-title">Can't Find What You're Looking For?</h2>
            <p class="section-subtitle mb-lg">
                Create your own unique bracelet with our customization tool. 
                Choose your gemstones, charms, and size for a truly personal piece.
            </p>
            <button class="btn-gemstone"><a href="BraceletCustomization" style="color: inherit; text-decoration: none;">Customize Your Bracelet</a></button>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>AURA ARTIFACTS</h3>
                    <p>
                        Handcrafted gemstone bracelets that harmonize your energy and elevate 
                        your style. Made with love, intention, and sustainable practices.
                    </p>
                </div>

                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <p><a href="index">Home</a></p>
                    <p><a href="collections">Collections</a></p>
                    <p><a href="about">About Us</a></p>
                    <p><a href="contact">Contact</a></p>
                </div>

                <div class="footer-section">
                    <h3>customer Care</h3>
                    <p><a href="SizeGuide">Size Guide</a></p>
                    <p><a href="GemstoneMeanings">Gemstone Meanings</a></p>
                    <p><a href="help">Help</a></p>
                    <p><a href="Accessibility">Accessibility</a></p>
                </div>

                <div class="footer-section">
                    <h3>Connect With Us</h3>
                    <p>Email: <a href="mailto:info@auraartifacts.com">info@auraartifacts.com</a></p>
                    <p>Phone: +966 50 123 4567</p>
                    <p>Instagram: @auraartifacts</p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 Aura Artifacts. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/collections.js"></script>

</body>
</html>
