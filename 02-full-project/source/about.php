<?php require_once "config.php"; $cart_count = array_sum(array_column($_SESSION["cart"] ?? [], "quantity")); $is_logged = isset($_SESSION["customer_id"]); ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Learn about Aura Artifacts' commitment to sustainability, ethical sourcing, and the healing power of gemstones.">
    <title>About Us | Aura Artifacts</title>
    
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

    <!-- About Hero Section -->
    <section class="about-hero">
        <div class="container">
            <div class="about-content">
                <div class="about-text slide-in-left">
                    <h1>Our Story</h1>
                    <p>
                        Aura Artifacts was born from a deep reverence for nature's wisdom and 
                        the ancient healing power of gemstones. What began as a personal journey 
                        of spiritual discovery has blossomed into a mission to share the 
                        transformative energy of crystals with the world.
                    </p>
                    <p>
                        We believe that jewelry should be more than just beautiful-it should 
                        carry intention, meaning, and positive energy. Each bracelet we create 
                        is a sacred artifact, handcrafted with love and designed to support 
                        your unique path to wellness and self-discovery.
                    </p>
                    <p>
                        Our commitment extends beyond creating beautiful pieces. We honor the 
                        Earth by sourcing ethically, supporting artisan communities, and 
                        implementing sustainable practices at every step of our journey.
                    </p>
                </div>
                <div class="slide-in-right">
                    <img src="assets/images/ABOUT US.jpg" alt="Aura Artifacts Story - Flowers with Jewelry" class="about-image" style="width: 100%; height: auto; min-height: 500px; object-fit: cover; border-radius: 16px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values -->
    <section class="values section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Our Core Values</h2>
                <p class="section-subtitle">
                    The principles that guide everything we do
                </p>
            </div>

            <div class="grid grid-3">
                <div class="value-card fade-in">
                    <div class="value-icon">🌿</div>
                    <h3 class="value-title">Sustainability</h3>
                    <p class="value-description">
                        We are committed to protecting our planet. From ethical sourcing to 
                        eco-friendly packaging, every decision we make considers environmental 
                        impact. We plant a tree for every bracelet sold and use only 
                        biodegradable materials.
                    </p>
                </div>

                <div class="value-card fade-in">
                    <div class="value-icon">💎</div>
                    <h3 class="value-title">Authenticity</h3>
                    <p class="value-description">
                        Every gemstone is genuine and certified. We work directly with trusted 
                        suppliers who share our values of transparency and quality. No synthetic 
                        stones, no compromises-only nature's authentic treasures.
                    </p>
                </div>

                <div class="value-card fade-in">
                    <div class="value-icon">🤲</div>
                    <h3 class="value-title">Craftsmanship</h3>
                    <p class="value-description">
                        Each bracelet is lovingly handcrafted by skilled artisans who pour 
                        intention and care into every piece. We support fair wages and safe 
                        working conditions, ensuring that beauty is created ethically.
                    </p>
                </div>

                <div class="value-card fade-in">
                    <div class="value-icon">✨</div>
                    <h3 class="value-title">Healing Energy</h3>
                    <p class="value-description">
                        We honor the ancient wisdom of crystal healing. Each stone is cleansed 
                        and charged with positive intentions before reaching you, ready to 
                        support your emotional, spiritual, and physical well-being.
                    </p>
                </div>

                <div class="value-card fade-in">
                    <div class="value-icon">🌍</div>
                    <h3 class="value-title">Community</h3>
                    <p class="value-description">
                        We believe in giving back. A portion of every sale supports education 
                        and healthcare initiatives in the communities where our gemstones are 
                        sourced, creating positive ripples around the world.
                    </p>
                </div>

                <div class="value-card fade-in">
                    <div class="value-icon">💝</div>
                    <h3 class="value-title">customer Love</h3>
                    <p class="value-description">
                        Your satisfaction and spiritual journey matter to us. We offer 
                        personalized guidance, lifetime support, and a 30-day happiness 
                        guarantee because your trust is our greatest treasure.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Journey Timeline -->
    <section class="story-timeline">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Our Journey</h2>
                <p class="section-subtitle">
                    From a dream to a global movement
                </p>
            </div>

            <div style="max-width: 900px; margin: 0 auto;">
                <div class="timeline-item fade-in">
                    <div class="timeline-marker">🌱</div>
                    <div class="timeline-content">
                        <h3>2019 - The Seed is Planted</h3>
                        <p>
                            Our founder discovered the healing power of Rose Quartz during a 
                            personal journey of self-discovery. Inspired by its transformative 
                            energy, the vision for Aura Artifacts was born.
                        </p>
                    </div>
                </div>

                <div class="timeline-item fade-in">
                    <div class="timeline-marker">✨</div>
                    <div class="timeline-content">
                        <h3>2020 - First Collection Launch</h3>
                        <p>
                            We launched our first collection of handcrafted bracelets, featuring 
                            five signature designs. The response was overwhelming, with customers 
                            sharing stories of healing and transformation.
                        </p>
                    </div>
                </div>

                <div class="timeline-item fade-in">
                    <div class="timeline-marker">🌍</div>
                    <div class="timeline-content">
                        <h3>2021 - Going Global</h3>
                        <p>
                            Expanded to international shipping and established partnerships with 
                            ethical gemstone suppliers in India, Brazil, and Madagascar. Planted 
                            our first 1,000 trees.
                        </p>
                    </div>
                </div>

                <div class="timeline-item fade-in">
                    <div class="timeline-marker">🎨</div>
                    <div class="timeline-content">
                        <h3>2022 - Customization Tool</h3>
                        <p>
                            Introduced our innovative customization feature, allowing customers 
                            to design their own unique bracelets. Empowering personal expression 
                            and spiritual connection.
                        </p>
                    </div>
                </div>

                <div class="timeline-item fade-in">
                    <div class="timeline-marker">💚</div>
                    <div class="timeline-content">
                        <h3>2023 - Sustainability Milestone</h3>
                        <p>
                            Achieved carbon-neutral shipping and transitioned to 100% 
                            biodegradable packaging. Launched our community giveback program, 
                            supporting education in mining communities.
                        </p>
                    </div>
                </div>

                <div class="timeline-item fade-in">
                    <div class="timeline-marker">🌟</div>
                    <div class="timeline-content">
                        <h3>2024 - Global Recognition</h3>
                        <p>
                            Serving thousands of customers worldwide, with over 50,000 bracelets 
                            crafted and 10,000 trees planted. Featured in leading wellness magazines 
                            and recognized for our commitment to sustainability.
                        </p>
                    </div>
                </div>

                <div class="timeline-item fade-in">
                    <div class="timeline-marker">🚀</div>
                    <div class="timeline-content">
                        <h3>2025 - Innovation & Expansion</h3>
                        <p>
                            Launched our mobile app for virtual try-on and personalized gemstone 
                            recommendations. Opened our first physical boutique and expanded our 
                            artisan network to support more communities worldwide.
                        </p>
                    </div>
                </div>

                <div class="timeline-item fade-in">
                    <div class="timeline-marker">💫</div>
                    <div class="timeline-content">
                        <h3>2026 - Today & Beyond</h3>
                        <p>
                            Continuing our mission to spread healing, beauty, and positive energy. 
                            With over 100,000 bracelets crafted, 20,000 trees planted, and a thriving 
                            global community, we're just getting started. The future is bright! ✨
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Ethical Sourcing -->
    <section class="section">
        <div class="container">
            <div class="about-content">
                <div>
                    <video autoplay muted loop playsinline class="about-image" style="width: 100%; height: auto; border-radius: 16px; object-fit: cover;">
                        <source src="https://v.etsystatic.com/video/upload/ac_none,du_15,q_auto:good/jrcydbsvmd6rca8kiqtw.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
                <div class="about-text slide-in-right">
                    <h2>Ethical Sourcing</h2>
                    <p>
                        Every gemstone in our collection is sourced with the utmost care for 
                        both people and planet. We work exclusively with suppliers who adhere 
                        to strict ethical standards:
                    </p>
                    <div class="benefit-item">
                        <div class="benefit-icon">✓</div>
                        <div class="benefit-text">
                            <h4>Fair Labor Practices</h4>
                            <p>No child labor, fair wages, and safe working conditions for all miners and artisans.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">✓</div>
                        <div class="benefit-text">
                            <h4>Environmental Protection</h4>
                            <p>Responsible mining practices that minimize environmental impact and restore ecosystems.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">✓</div>
                        <div class="benefit-text">
                            <h4>Community Support</h4>
                            <p>Direct partnerships that ensure mining communities benefit from their natural resources.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">✓</div>
                        <div class="benefit-text">
                            <h4>Certification & Transparency</h4>
                            <p>Full traceability from mine to market, with certificates of authenticity for every stone.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Impact Stats -->
    <section class="section section-alt">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Our Impact</h2>
                <p class="section-subtitle">
                    Together, we're making a difference
                </p>
            </div>

            <div class="grid grid-4">
                <div class="card text-center">
                    <h3 style="font-size: 3rem; color: var(--accent); margin-bottom: 10px;">50K+</h3>
                    <p style="font-size: 1.125rem; color: var(--primary); font-weight: 600;">Bracelets Crafted</p>
                    <p style="font-size: 0.9375rem; color: var(--text-light);">Each one made with love and intention</p>
                </div>

                <div class="card text-center">
                    <h3 style="font-size: 3rem; color: var(--accent); margin-bottom: 10px;">10K+</h3>
                    <p style="font-size: 1.125rem; color: var(--primary); font-weight: 600;">Trees Planted</p>
                    <p style="font-size: 0.9375rem; color: var(--text-light);">One tree for every bracelet sold</p>
                </div>

                <div class="card text-center">
                    <h3 style="font-size: 3rem; color: var(--accent); margin-bottom: 10px;">25+</h3>
                    <p style="font-size: 1.125rem; color: var(--primary); font-weight: 600;">Countries Served</p>
                    <p style="font-size: 0.9375rem; color: var(--text-light);">Spreading healing energy worldwide</p>
                </div>

                <div class="card text-center">
                    <h3 style="font-size: 3rem; color: var(--accent); margin-bottom: 10px;">100%</h3>
                    <p style="font-size: 1.125rem; color: var(--primary); font-weight: 600;">Carbon Neutral</p>
                    <p style="font-size: 0.9375rem; color: var(--text-light);">Eco-friendly shipping & packaging</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Join Our Journey -->
    <section class="section">
        <div class="container text-center">
            <h2 class="section-title">Join Our Journey</h2>
            <p class="section-subtitle mb-lg" style="max-width: 700px; margin-left: auto; margin-right: auto;">
                Become part of a global community that values beauty, healing, and sustainability. 
                Discover your perfect gemstone companion and start your transformation today.
            </p>
            <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                <a href="collections" class="btn-gemstone">Shop Collections</a>
                <a href="BraceletCustomization" class="btn-gemstone-secondary">Customize Your Bracelet</a>
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
            
            if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
                searchResults.style.display = 'none';
            }
            
            if (!menuBtn.contains(event.target) && !menuDropdown.contains(event.target)) {
                menuDropdown.style.display = 'none';
            }
        });
    </script>

</body>
</html>
