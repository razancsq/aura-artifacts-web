<?php require_once "config.php"; $cart_count = array_sum(array_column($_SESSION["cart"] ?? [], "quantity")); $is_logged = isset($_SESSION["customer_id"]); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Design Your Bracelet</title>

    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&family=Lato:wght@400;700&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="assets/css/styles.css">

    <link rel="stylesheet" href="assets/css/pages.css">
</head>
<body>

    <?php include "includes/navbar.php"; ?>

    <main class="container section">
        <div class="section-header text-center">
            <h1 class="section-title">Design Your <span class="highlight-word" style="background:linear-gradient(135deg,#FF9EBB,#8DD4CC);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Unique</span> Bracelet</h1>
            <p class="order-note-box">Note: You must add 10 or more charms to complete your order.<br><small style="opacity:0.85;">1 Charm = 1 Cm</small></p>
        </div>

        <div class="text-center">
            <div class="price-box-custom">
                <h3 style="margin-bottom: 5px; font-family: var(--font-body); font-size: 1rem; color: var(--text-light);">CURRENT TOTAL</h3>
                <div class="card-price" id="total-price" style="font-size: 3rem;">$ 0</div>
                <p>Selected: <span id="charm-count" style="font-weight: bold; color: var(--primary);">0</span> / 10 Charms</p>
            </div>
        </div>

        <div class="preview-zone" id="bracelet-preview">
            <p id="placeholder-text" style="color: var(--text-light);">Your design will appear here</p>
        </div>

        <div class="hero-buttons">
            <button class="btn-gemstone-secondary" onclick="deleteLast()">Undo</button>
            <button class="btn-gemstone-secondary" onclick="location.reload()">Clear All</button>
            <button id="add-to-cart-btn" class="btn-gemstone" disabled onclick="goToCart()">Add to cart</button>
        </div>

        <div class="divider"></div>

        <div id="all-categories-container"></div>
    </main>

    <footer class="footer" style="background-color: var(--primary); color: white; padding: 60px 0 30px 0; margin-top: 80px;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; text-align: left; margin-bottom: 40px;">
                <div>
                    <h3 style="font-family: var(--font-heading); font-size: 1.8rem; margin-bottom: 20px; color: var(--accent);">AURA ARTIFACTS</h3>
                    <p style="font-size: 0.9rem; opacity: 0.8; color: white">Quality and precision in every bead.</p>
                    <p style="font-size: 0.9rem; opacity: 0.8; margin-top: 10px;"><a href="about" style="color: white; text-decoration: none;">About us</a></p>
                </div>
                <div>
                    <h4 style="margin-bottom: 20px; font-size: 1.4rem; font-family: var(--font-heading); border-bottom: 1px solid rgba(255,255,255,0.1); color: var(--accent); padding-bottom: 10px;">Navigation</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 10px;"><a href="index" style="color: white; text-decoration: none; font-size: 0.9rem; opacity: 0.8;">HOME</a></li>
                        <li style="margin-bottom: 10px;"><a href="BraceletCustomization" style="color: white; text-decoration: none; font-size: 0.9rem; opacity: 0.8;">CUSTOMIZATION</a></li>
						<li style="margin-bottom: 10px;"><a href="collections" style="color: white; text-decoration: none; font-size: 0.9rem; opacity: 0.8;">COLLECTIONS</a></li>
                        <li style="margin-bottom: 10px;"><a href="GemstoneMeanings" style="color: white; text-decoration: none; font-size: 0.9rem; opacity: 0.8;">GEMSTONE MEANINGS</a></li>
                    </ul>
                </div>
                <div>
                    <h4 style="margin-bottom: 20px; font-size: 1.4rem; font-family: var(--font-heading); border-bottom: 1px solid rgba(255,255,255,0.1); color: var(--accent); padding-bottom: 10px;">Pages</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 10px;"><a href="help" style="color: white; text-decoration: none; font-size: 0.9rem; opacity: 0.8;">• Help / FAQ</a></li>
                        <li style="margin-bottom: 10px;"><a href="Accessibility" style="color: white; text-decoration: none; font-size: 0.9rem; opacity: 0.8;">• Accessibility Info</a></li>
                    </ul>
                </div>
            </div>
            <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; text-align: center;">
                <p style="font-size: 0.8rem; opacity: 0.6;">&copy; 2026 Aura Artifacts. Professional Project.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/bracelet-customization.js?v=<?= time() ?>"></script>
</body>
</html>

