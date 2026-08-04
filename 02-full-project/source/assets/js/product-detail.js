// ============================================================
// product-detail.js - Product Detail Page Scripts
// Handles: Gallery, Quantity, Cart, Wishlist, Search
// ============================================================

'use strict';

// --- Local Cart Management ---
function getCart() {
    const cart = localStorage.getItem('cart');
    return cart ? JSON.parse(cart) : [];
}

function saveCart(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function addToCart() {
    const quantity = parseInt(document.getElementById('quantity').value);
    const product = {
        id: 'earrings-pink-tourmaline',
        name: 'Pink Tourmaline Drop Earrings',
        price: 450,
        quantity: quantity,
        image: 'assets/images/firstpick-1.jpg',
        url: 'product-detail?id=1'
    };

    let cart = getCart();
    const existingItem = cart.find(item => item.id === product.id);

    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push(product);
    }

    saveCart(cart);
    if (typeof window.showErrorToast === 'function') window.showErrorToast(`Added ${quantity} item(s) to cart! 🛒`, 'success');
}

// --- Image Gallery ---
function changeImage(imageSrc, index) {
    document.getElementById('mainImage').src = imageSrc;
    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach((thumb, i) => {
        thumb.classList.remove('active');
        if (i === index) thumb.classList.add('active');
    });
}

// --- Quantity ---
function increaseQuantity() {
    const input = document.getElementById('quantity');
    if (input.value < 2) {
        input.value = parseInt(input.value) + 1;
    } else {
        if (typeof window.showErrorToast === 'function') window.showErrorToast('Maximum quantity is 2 items per order', 'warning');
    }
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    if (input.value > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

// --- Wishlist ---
function addToWishlist() {
    const product = {
        id: 'earrings-pink-tourmaline',
        name: 'Pink Tourmaline Drop Earrings',
        price: 450,
        image: 'assets/images/firstpick-1.jpg',
        url: 'product-detail?id=1'
    };

    let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    const existingItem = favorites.find(item => item.id === product.id);

    if (!existingItem) {
        favorites.push(product);
        localStorage.setItem('favorites', JSON.stringify(favorites));
        window.showErrorToast && window.showErrorToast('Added to Favorites! 💎', 'success');
    } else {
        window.showErrorToast && window.showErrorToast('Already in your favorites!', 'warning');
    }
}

// --- Search ---
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

    if (input.length === 0) { resultsDiv.style.display = 'none'; return; }

    const filtered = products.filter(p =>
        p.name.toLowerCase().includes(input) || p.category.toLowerCase().includes(input)
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

// --- Close menus on outside click ---
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
