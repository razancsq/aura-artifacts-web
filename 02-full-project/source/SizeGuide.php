<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bracelet Size Guide</title>
    
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        
        .guide-main-layout {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 40px;
            margin-top: 50px;
            align-items: start;
        }

        .steps-container {
            text-align: left;
        }

        .step-card {
            background: #ffffff;
            border: 1px solid var(--cream);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            display: flex;
            gap: 20px;
            transition: 0.3s ease;
        }

        .step-card:hover {
            border-color: var(--accent);
            transform: scale(1.02);
        }

        .step-circle {
            background: var(--primary);
            color: white;
            min-width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .step-content h3 {
            font-family: var(--font-heading);
            color: var(--primary);
            margin-bottom: 8px;
        }

        .chart-wrapper {
            background: var(--cream);
            padding: 30px;
            border-radius: 20px;
            position: sticky;
            top: 100px;
        }

        .size-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .size-table caption {
            padding: 15px;
            font-family: var(--font-heading);
            color: var(--primary);
            font-weight: bold;
        }

        .size-table th, .size-table td {
            padding: 18px;
            border: 1px solid #f0f0f0;
            text-align: center;
        }

        .size-table th {
            background: var(--primary);
            color: white;
            font-weight: 500;
            font-family: var(--font-body);
        }

        .tip-box {
            margin-top: 20px;
            padding: 15px;
            background: rgba(212, 175, 55, 0.1);
            border-left: 4px solid var(--accent);
            font-size: 0.9rem;
            line-height: 1.5;
        }
		
		.highlight-word {
            background: linear-gradient(135deg, #FFD1E0, var(--turquoise-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @media (max-width: 992px) {
            .guide-main-layout { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <?php include "includes/navbar.php"; ?>

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

    <main class="container section">
        <header class="section-header text-center">
            <h1 class="section-title">Measure Your <span class="highlight-word">Wrist</span></h1>
            <p>Accuracy is the key to elegance. Follow our professional guide below.</p>
        </header>

        <div class="guide-main-layout">
            
            <section class="steps-container">
                <article class="step-card">
                    <div class="step-circle">1</div>
                    <div class="step-content">
                        <h3>Identify the Spot</h3>
                        <p>Locate the area just below your wrist bone where you typically wear a bracelet.</p>
                    </div>
                </article>

                <article class="step-card">
                    <div class="step-circle">2</div>
                    <div class="step-content">
                        <h3>Wrap & Mark</h3>
                        <p>Use a measuring tape or string. Wrap it comfortably around your wrist and mark the intersection.</p>
                    </div>
                </article>

                <article class="step-card">
                    <div class="step-circle">3</div>
                    <div class="step-content">
                        <h3>Check the Ruler</h3>
                        <p>Lay the string flat against a ruler. The length in centimeters is your wrist size.</p>
                    </div>
                </article>

            </section>

            <aside class="chart-wrapper text-center">
                <h2 style="font-family: var(--font-heading); color: var(--primary); margin-bottom: 20px;">Size Guide</h2>
                
                <table class="size-table">
                    <caption>Bracelet and Wrist Size Comparison</caption>
                    <thead>
                        <tr>
                            <th>Wrist (cm)</th>
                            <th>Recommended</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>14 - 15 cm</td>
                            <td>Small (17 cm)</td>
                        </tr>
                        <tr>
                            <td>16 - 17 cm</td>
                            <td>Medium (19 cm)</td>
                        </tr>
                        <tr>
                            <td>18 - 19 cm</td>
                            <td>Large (21 cm)</td>
                        </tr>
                        <tr>
                            <td>20 - 21 cm</td>
                            <td>X-Large (23 cm)</td>
                        </tr>
                    </tbody>
                </table>
            </aside>

        </div>
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