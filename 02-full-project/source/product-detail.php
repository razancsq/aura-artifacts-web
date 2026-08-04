<?php
require_once 'config.php';
$cart_count = array_sum(array_column($_SESSION['cart'] ?? [], 'quantity'));
$is_logged  = isset($_SESSION['customer_id']);

$product_id_page = (int)($_GET['id'] ?? 1);

// Fetch product details
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM product p LEFT JOIN category c ON p.category_id = c.category_id WHERE p.product_id = ?");
$stmt->bind_param("i", $product_id_page);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) { header("Location: collections"); exit(); }

// Track recently viewed
$viewed = [];
if (isset($_COOKIE['recently_viewed'])) {
    $viewed = array_filter(array_map('intval', explode(',', $_COOKIE['recently_viewed'])));
}
$viewed = array_values(array_filter($viewed, fn($v) => $v !== $product_id_page));
array_unshift($viewed, $product_id_page);
$viewed = array_slice($viewed, 0, 5);
setcookie('recently_viewed', implode(',', $viewed), time()+2592000, '/');
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($product['description']) ?>">
    <title><?= htmlspecialchars($product['name']) ?> | Aura Artifacts</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- External CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <link rel="stylesheet" href="assets/css/pages.css">
</head>
<body>

    <!-- Navigation -->
    <?php include "includes/navbar.php"; ?>

    <!-- Breadcrumb -->
    <section class="breadcrumb-section" style="background: var(--cream); padding: var(--space-md) 0; border-bottom: 1px solid var(--secondary);">
        <div class="container">
            <a href="index" style="color: var(--text-light); text-decoration: none;">Home</a>
            <span style="color: var(--text-light); margin: 0 var(--space-sm);">/</span>
            <a href="collections" style="color: var(--text-light); text-decoration: none;">Collections</a>
            <span style="color: var(--text-light); margin: 0 var(--space-sm);">/</span>
            <span style="color: var(--primary); font-weight: 600;"><?= htmlspecialchars($product['name']) ?></span>
        </div>
    </section>

    <?php if(isset($_GET['cart']) && $_GET['cart'] === 'success'): ?>
        <div class="container" style="margin-top: 20px;">
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; text-align: center;">
                Item added to your cart successfully! <a href="cart" style="font-weight: bold; color: #155724;">View cart</a>
            </div>
        </div>
    <?php endif; ?>
    <?php if(isset($_GET['wishlist']) && $_GET['wishlist'] === 'success'): ?>
        <div class="container" style="margin-top: 20px;">
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; text-align: center;">
                Item added to your wishlist! <a href="profile" style="font-weight: bold; color: #155724;">View in Profile</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main product Section -->
    <section class="section">
        <div class="container">
            <div class="product-detail-container">
                
                <!-- Gallery Section -->
                <div class="product-gallery">
                    <div class="main-image-wrapper">
                        <img id="mainImage" src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="main-image" onerror="this.src='assets/images/placeholder.png';">
                    </div>
                    
                    <div class="thumbnail-gallery">
                        <div class="thumbnail active" onclick="changeImage('<?= htmlspecialchars($product['image_url']) ?>', 0)">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="product Display" onerror="this.src='assets/images/placeholder.png';">
                        </div>
                        <?php if (!empty($product['image_url_2'])): ?>
                        <div class="thumbnail" onclick="changeImage('<?= htmlspecialchars($product['image_url_2']) ?>', 1)">
                            <img src="<?= htmlspecialchars($product['image_url_2']) ?>" alt="On Body" onerror="this.src='assets/images/placeholder.png';">
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($product['image_url_3'])): ?>
                        <div class="thumbnail" onclick="changeImage('<?= htmlspecialchars($product['image_url_3']) ?>', 2)">
                            <img src="<?= htmlspecialchars($product['image_url_3']) ?>" alt="Packaging" onerror="this.src='assets/images/placeholder.png';">
                        </div>
                        <?php endif; ?>
                    </div>


                </div>

                <!-- product Info Section -->
                <div class="product-info-section">
                    
                    <div class="product-header">
                        <span class="product-category-badge"><?= htmlspecialchars($product['category_name']) ?></span>
                        <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
                    </div>

                    <div class="product-price-section">
                        <span class="product-price">$<?= number_format($product['base_price'], 2) ?></span>
                    </div>

                    <p class="product-description">
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                    </p>

                    <div class="gemstone-properties">
                        <span class="property-tag">Handcrafted</span>
                        <span class="property-tag">Premium Quality</span>
                    </div>

                    <div class="product-details-list">
                        <div class="detail-item">
                            <span class="detail-label">Material</span>
                            <span class="detail-value">Pink Morganite & Rose Gold</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Stone Cut</span>
                            <span class="detail-value">Pear-cut Morganite with Brilliant Diamonds</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Weight</span>
                            <span class="detail-value">8g per pair</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Style</span>
                            <span class="detail-value">Art Deco Inspired</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Shipping</span>
                            <span class="detail-value">Free Worldwide</span>
                        </div>
                    </div>

                    <form action="actions/cart_add.php" method="POST">
                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                        <div class="quantity-selector">
                            <span class="quantity-label">Quantity:</span>
                            <div class="quantity-input">
                                <button type="button" class="quantity-btn" onclick="decreaseQuantity()">−</button>
                                <input type="number" id="quantity" name="quantity" class="quantity-value" value="1" min="1" max="10" readonly>
                                <button type="button" class="quantity-btn" onclick="increaseQuantity()">+</button>
                            </div>
                        </div>

                        <div class="action-buttons" style="align-items: center;">
                            <button type="submit" class="btn-add-cart">Add to cart</button>
                            <a href="actions/wishlist_add.php?id=<?= $product['product_id'] ?>" style="text-decoration:none; margin-left: 10px;">
                                <span class="gem-icon-product" title="Add to Favorites" style="cursor: pointer; font-size: 1.8rem; display: inline-block; padding: 10px;">💎</span>
                            </a>
                        </div>
                    </form>

                    <!-- Additional Info -->
                    <div class="additional-info">
                        <div class="info-row">
                            <span class="info-label">Delivery</span>
                            <span class="info-value">2-3 days | Store pickup available</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Warranty</span>
                            <span class="info-value">Lifetime warranty | 30-day returns</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Care</span>
                            <span class="info-value">Hypoallergenic posts | Secure butterfly backs</span>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- Related Products Section -->
    <section class="section section-alt">
        <div class="container">
            <h2 class="section-title" style="text-align: center; margin-bottom: var(--space-lg);">You Might Also Like</h2>
            
            <div class="grid grid-3">
                <div class="product-card fade-in">
                    <a href="product-detail?id=2" style="text-decoration: none; color: inherit;">
                        <div class="product-image-wrapper">
                            <img src="assets/images/secoundpick_1.jpg" alt="Golden Radiance" class="product-image">
                            <span class="product-badge">New Arrival</span>
                        </div>
                        <div class="product-info">
                            <span class="product-category">Ring</span>
                            <h3 class="product-name">Golden Radiance</h3>
                            <p class="product-description">Premium Topaz stone in gold-plated band.</p>
                            <div class="product-footer">
                                <span class="product-price">$550</span>
                                <button class="btn-gemstone">View</button>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="product-card fade-in">
                    <a href="product-detail?id=3" style="text-decoration: none; color: inherit;">
                        <div class="product-image-wrapper">
                            <img src="assets/images/thirdpick_1.jpg" alt="Royal Purple" class="product-image">
                            <span class="product-badge">Limited Edition</span>
                        </div>
                        <div class="product-info">
                            <span class="product-category">Necklace</span>
                            <h3 class="product-name">Royal Purple</h3>
                            <p class="product-description">Stunning Amethyst stones on delicate chain.</p>
                            <div class="product-footer">
                                <span class="product-price">$420</span>
                                <button class="btn-gemstone">View</button>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="product-card fade-in">
                    <a href="product-detail?id=4" style="text-decoration: none; color: inherit;">
                        <div class="product-image-wrapper">
                            <img src="assets/images/FOURTHPICK-1.jpg" alt="Emerald Essence" class="product-image">
                        </div>
                        <div class="product-info">
                            <span class="product-category">Pendant</span>
                            <h3 class="product-name">Emerald Essence</h3>
                            <p class="product-description">Rich Emerald crystal pendant on silver chain.</p>
                            <div class="product-footer">
                                <span class="product-price">$2500</span>
                                <button class="btn-gemstone">View</button>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
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
                    <p><a href="#contact">Contact</a></p>
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
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 Aura Artifacts. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/product-detail.js"></script>

    <!-- Task 14: Pop-up Help Window -->
    <button id="helpBtn" onclick="openHelpPopup()" title="Need Help?" style="
        position: fixed; bottom: 30px; right: 30px; z-index: 9999;
        width: 56px; height: 56px; border-radius: 50%; border: none;
        background: linear-gradient(135deg, var(--accent, #b08d57), var(--primary, #2d3e50));
        color: white; font-size: 1.6rem; font-weight: 700; cursor: pointer;
        box-shadow: 0 6px 24px rgba(0,0,0,0.25); transition: all 0.3s ease;
        display: flex; align-items: center; justify-content: center;
    " onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">?</button>

    <script>
    // Task 14: Pop-up Help Window (uses window.open for a true pop-up)
    function openHelpPopup() {
        var helpContent = `
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Help - Aura Artifacts</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: 'Poppins', sans-serif; background: #faf8f5; color: #2d3e50; padding: 25px; line-height: 1.7; }
                h1 { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: #2d3e50; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #b08d57; }
                .help-section { background: white; border-radius: 12px; padding: 18px; margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); border-left: 4px solid #b08d57; }
                .help-section h3 { color: #b08d57; font-size: 0.95rem; margin-bottom: 8px; }
                .help-section p, .help-section li { font-size: 0.85rem; color: #555; }
                .help-section ul { padding-left: 18px; }
                .help-section li { margin-bottom: 4px; }
                .close-btn { display: block; width: 100%; padding: 12px; background: linear-gradient(135deg, #b08d57, #2d3e50); color: white; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer; margin-top: 10px; transition: opacity 0.3s; }
                .close-btn:hover { opacity: 0.9; }
            </style>
        </head>
        <body>
            <h1>💎 product Help Guide</h1>

            <div class="help-section">
                <h3>🛒 How to order</h3>
                <ul>
                    <li>Select the desired quantity using the +/− buttons</li>
                    <li>Click <strong>"Add to cart"</strong> to add the item</li>
                    <li>Proceed to <strong>Checkout</strong> when ready to purchase</li>
                    <li>You must be logged in to complete your order</li>
                </ul>
            </div>

            <div class="help-section">
                <h3>📏 Sizing Guide</h3>
                <p>All our jewelry is handcrafted to standard sizes. For bracelets and rings, please visit our <strong>Size Guide</strong> page for detailed measurements. Custom sizing is available upon request.</p>
            </div>

            <div class="help-section">
                <h3>✨ Gemstone Care</h3>
                <ul>
                    <li>Avoid contact with perfume, lotion, and chemicals</li>
                    <li>Store in a cool, dry place away from direct sunlight</li>
                    <li>Clean gently with a soft cloth</li>
                    <li>Remove jewelry before swimming or showering</li>
                </ul>
            </div>

            <div class="help-section">
                <h3>📦 Shipping & Delivery</h3>
                <p>Free worldwide shipping on all orders. Standard delivery takes <strong>2-3 business days</strong>. Store pickup is also available at our Al Jubail location.</p>
            </div>

            <div class="help-section">
                <h3>↩️ Returns & Warranty</h3>
                <p>We offer a <strong>30-day return policy</strong> and <strong>lifetime warranty</strong> on all handcrafted pieces. Items must be returned in original condition with packaging.</p>
            </div>

            <button class="close-btn" onclick="window.close()">Close Help Window</button>
        </body>
        </html>`;

        var helpWin = window.open('', 'AuraHelp', 'width=480,height=620,scrollbars=yes,resizable=yes,left=200,top=100');
        helpWin.document.write(helpContent);
        helpWin.document.close();
        helpWin.focus();
    }
    </script>

</body>
</html>