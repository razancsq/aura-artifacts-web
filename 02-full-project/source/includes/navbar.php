<?php
// Determine correct base path for assets (works from root and admin/ subdirectory)
$_navBasePath = (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../' : '';
?>
<script src="<?= $_navBasePath ?>assets/js/error-handler.js"></script>
<script src="<?= $_navBasePath ?>assets/js/currency-converter.js"></script>
<link rel="stylesheet" href="<?= $_navBasePath ?>assets/css/dark-mode.css">
<script src="<?= $_navBasePath ?>assets/js/dark-mode.js"></script>
<style>
.nav-search { position: relative; margin: 0 15px; }
.search-input { padding: 8px 15px; border: none; border-bottom: 1.5px solid #D4AF37; border-radius: 0; width: 200px; font-size: 0.9rem; transition: 0.3s; background: transparent; text-align: center; font-family: var(--font-body); }
.search-input::placeholder { color: #B8B8B8; font-style: italic; }
.search-input:focus { outline: none; border-bottom-color: var(--accent); box-shadow: none; }
.search-results { position: absolute; top: 100%; left: 0; background: white; border: 2px solid var(--accent); border-radius: 12px; min-width: 250px; max-height: 300px; overflow-y: auto; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 1000; margin-top: 5px; }
.search-item { padding: 12px 15px; border-bottom: 1px solid var(--secondary); cursor: pointer; transition: 0.2s; display: block; text-decoration: none; color: inherit; }
.search-item:hover { background: var(--cream); padding-left: 20px; }
.search-item:last-child { border-bottom: none; }
.search-item-name { font-weight: 600; color: var(--primary); }
.search-item-category { font-size: 0.8rem; color: var(--text-light); }
.nav-dropdown { position: relative; }
.nav-menu-btn { background: none; border: none; cursor: pointer; font-size: 1rem; padding: 0; color: var(--primary); }
.menu-dropdown { position: absolute; top: 100%; right: 0; background: white; border: 2px solid var(--accent); border-radius: 12px; min-width: 200px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 1000; margin-top: 5px; }
.menu-item { display: block; padding: 12px 20px; color: var(--primary); text-decoration: none; transition: 0.2s; border-bottom: 1px solid var(--secondary); }
.menu-item:hover { background: var(--cream); padding-left: 25px; }
.menu-item:last-child { border-bottom: none; }
.gem-icon { transition: 0.3s; }
.gem-icon:hover { transform: scale(1.2); filter: drop-shadow(0 0 8px rgba(255, 158, 187, 0.6)); }
.favorites-dropdown { animation: slideDown 0.3s ease-out; }
@keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>
<nav class="navbar">
        <div class="nav-container">
            <a href="/aura/" class="logo">AURA ARTIFACTS</a>
            <ul class="nav-menu">
                <li><a href="/aura/" class="nav-link">Home</a></li>
                <li><a href="collections" class="nav-link">Collections</a></li>
                <li><a href="about" class="nav-link">About Us</a></li>
                <li><a href="contact" class="nav-link">Contact</a></li>
                <?php if(isset($_SESSION['customer_id'])): ?>
                    <li><a href="profile" class="nav-link">Profile</a></li>
                    <li><a href="actions/logout.php" class="nav-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="login" class="nav-link">Login</a></li>
                    <li><a href="register" class="nav-link">Register</a></li>
                <?php endif; ?>
                
                <!-- Search Bar -->
                <li class="nav-search" style="position: relative;">
                    <span style="position: absolute; left: 0; top: 50%; transform: translateY(-50%); font-size: 0.9rem; color: #D4AF37;">🔍</span>
                    <input type="text" id="searchInput" class="search-input" placeholder="Search our collection..." onkeyup="searchProducts()" style="padding-left: 25px;">
                    <div id="searchResults" class="search-results" style="display: none;"></div>
                </li>
                
                <!-- Icons -->
                <li><a href="cart" class="nav-link nav-icon" title="cart">🛒</a></li>
                <li><button id="darkModeToggle" class="dark-mode-toggle" title="Toggle Dark Mode" aria-label="Toggle Dark Mode">🌙</button></li>
                <li style="position: relative;">
                    <span class="gem-icon" onclick="toggleFavorites()" title="wishlist" style="cursor: pointer; font-size: 1.5rem; display: inline-block; padding: 5px 10px;">💎</span>
                    <div id="favorites-dropdown" class="favorites-dropdown" style="display: none; position: absolute; top: 100%; right: 0; background: white; border: 2px solid var(--accent); border-radius: 12px; min-width: 300px; max-height: 400px; overflow-y: auto; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 1000;">
                        <div id="favorites-content" style="padding: 16px;"></div>
                    </div>
                </li>

<script>
function toggleFavorites() {
    const dropdown = document.getElementById('favorites-dropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    if (dropdown.style.display === 'block') loadFavorites();
}

function loadFavorites() {
    const content = document.getElementById('favorites-content');
    content.innerHTML = '<div style="text-align:center;color:#aaa;padding:10px;">Loading...</div>';
    fetch('actions/wishlist_get.php')
        .then(r => r.json())
        .then(data => {
            if (!data.logged_in) {
                content.innerHTML = '<div style="text-align:center;color:#888;padding:12px;">Please <a href="login" style="color:#D4AF37;font-weight:600;">login</a> to view your wishlist</div>';
                return;
            }
            if (data.items.length === 0) {
                content.innerHTML = '<div style="text-align:center;color:#888;padding:12px;">Your wishlist is empty</div>';
                return;
            }
            let html = '';
            data.items.forEach(item => {
                html += `
                <div style="display:flex;align-items:center;gap:8px;padding:8px;border:1px solid #f0f0f0;border-radius:8px;margin-bottom:8px;background:#fff;">
                    <a href="product-detail?id=${item.product_id}" style="flex-shrink:0;">
                        <img src="${item.image_url}" alt="${item.name}" style="width:40px;height:40px;object-fit:cover;border-radius:6px;" onerror="this.src='assets/images/placeholder.png'">
                    </a>
                    <div style="flex:1;min-width:0;">
                        <a href="product-detail?id=${item.product_id}" style="font-weight:600;color:#2d3e50;font-size:0.8rem;text-decoration:none;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.name}</a>
                        <div style="color:#D4AF37;font-weight:700;font-size:0.75rem;">$${parseFloat(item.base_price).toFixed(2)}</div>
                    </div>
                    <button onclick="removeWishlistItem(${item.product_id})" style="background:none;border:none;color:#ff6b6b;cursor:pointer;font-size:1rem;padding:0;flex-shrink:0;" title="Remove">✕</button>
                </div>`;
            });
            html += '<a href="profile" style="display:block;text-align:center;margin-top:8px;color:#D4AF37;font-size:0.85rem;font-weight:600;text-decoration:none;">View Full wishlist →</a>';
            content.innerHTML = html;
        })
        .catch(() => {
            content.innerHTML = '<div style="text-align:center;color:#c0392b;padding:12px;">Error loading wishlist</div>';
        });
}

function removeWishlistItem(productId) {
    fetch('actions/wishlist_get.php?remove=' + productId)
        .then(r => r.json())
        .then(() => loadFavorites());
}

function toggleMenu() {
    const menu = document.getElementById('menuDropdown');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('favorites-dropdown');
    const gemIcon = document.querySelector('.gem-icon');
    if (dropdown && gemIcon && !dropdown.contains(event.target) && !gemIcon.contains(event.target)) {
        dropdown.style.display = 'none';
    }
    const menuDropdown = document.getElementById('menuDropdown');
    const menuBtn = document.querySelector('.nav-menu-btn');
    if (menuBtn && !menuBtn.contains(event.target) && menuDropdown && !menuDropdown.contains(event.target)) {
        menuDropdown.style.display = 'none';
    }
    const searchResults = document.getElementById('searchResults');
    const searchInput = document.getElementById('searchInput');
    if (searchInput && !searchInput.contains(event.target) && searchResults && !searchResults.contains(event.target)) {
        searchResults.style.display = 'none';
    }
});

const _navProducts = [
    { name: 'Pink Tourmaline Drop Earrings', category: 'Earrings', url: 'product-detail?id=1' },
    { name: 'Amethyst Tennis Bracelet', category: 'Bracelet', url: 'product-detail?id=2' },
    { name: 'Diamond Solitaire Necklace', category: 'Pendant', url: 'product-detail?id=3' },
    { name: 'Blue Zircon Ring', category: 'Ring', url: 'product-detail?id=4' },
    { name: 'Rose Gold Anklet', category: 'Anklet', url: 'product-detail?id=5' },
    { name: 'Sunglasses Charm Bracelet', category: 'Bracelet', url: 'product-detail?id=6' }
];

function searchProducts() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const resultsDiv = document.getElementById('searchResults');
    if (input.length === 0) { resultsDiv.style.display = 'none'; return; }
    const filtered = _navProducts.filter(p => p.name.toLowerCase().includes(input) || p.category.toLowerCase().includes(input));
    if (filtered.length === 0) {
        resultsDiv.innerHTML = '<div style="padding:15px;text-align:center;color:#888;">No products found</div>';
    } else {
        resultsDiv.innerHTML = filtered.map(p =>
            `<a href="${p.url}" class="search-item"><div class="search-item-name">${p.name}</div><div class="search-item-category">${p.category}</div></a>`
        ).join('');
    }
    resultsDiv.style.display = 'block';
}
</script>
                
                <!-- Menu Dropdown -->
                <li class="nav-dropdown">
                    <button class="nav-link nav-menu-btn" onclick="toggleMenu()">☰ Menu</button>
                    <div id="menuDropdown" class="menu-dropdown" style="display: none;">
                        <a href="Accessibility" class="menu-item">Accessibility</a>
                        <a href="help" class="menu-item">Help</a>
                        <a href="SizeGuide" class="menu-item">Size Guide</a>
                        <a href="GemstoneMeanings" class="menu-item">Gemstone Meanings</a>
                        <hr style="margin: 8px 0; border: none; border-top: 1px solid var(--secondary);">
                        <a href="admin/admin-login" class="menu-item">Admin Portal</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>