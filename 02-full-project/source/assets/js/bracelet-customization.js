// ============================================================
// bracelet-customization.js - Bracelet Builder Scripts
// Handles: Charm selection, preview, cart submission, search
// ============================================================

'use strict';

// --- Charm Folders Configuration ---
const charmFolders = [
    "Avengers", "BasicPlainCollection", "Black", "BlackAlphabet",
    "Cars", "Cherry", "Green", "InsideOut", "Months",
    "Pink", "PinkAlphabet", "Pinknumber", "Rednumber"
];

const container = document.getElementById('all-categories-container');
let selectedCharms = [];

// --- Build Category Grid ---
charmFolders.forEach(folder => {
    const section = document.createElement('section');
    section.className = 'category-section';

    let charmsHTML = '';
    for (let i = 1; i <= 5; i++) {
        charmsHTML += `
            <article class="card charm-card" onclick="addCharm('charms/${folder}/${folder}${i}.jpeg', 5)">
                <div class="card-image-wrapper">
                    <img src="charms/${folder}/${folder}${i}.jpeg"
                         class="card-image"
                         onerror="this.onerror=null; this.src='charms/${folder}/${folder}${i}.jpg';"
                         alt="${folder} charm number ${i}">
                </div>
                <h4 class="text-center" style="font-size: 1rem; margin: 8px 0 2px 0;">${folder} ${i}</h4>
                <div class="card-price text-center" style="font-size: 1.2rem; color: var(--accent);">+$ 5</div>
            </article>
        `;
    }

    section.innerHTML = `
        <h3 class="category-title">${folder}</h3>
        <div class="charms-grid">${charmsHTML}</div>
    `;
    container.appendChild(section);
});

// --- State ---
let total = 0;
let count = 0;

// --- Add Charm ---
function addCharm(imgSrc, price) {
    const preview = document.getElementById('bracelet-preview');
    const placeholder = document.getElementById('placeholder-text');
    if (placeholder) placeholder.style.display = 'none';

    const img = document.createElement('img');
    img.src = imgSrc;
    img.className = 'added-charm';
    img.dataset.price = price;
    img.alt = "Selected charm on bracelet";

    img.onerror = function () {
        if (this.src.endsWith('.jpeg')) {
            this.src = this.src.replace('.jpeg', '.jpg');
        }
        this.onerror = null;
    };

    preview.appendChild(img);
    selectedCharms.push(imgSrc);
    total += price;
    count++;
    updateDisplay();
}

// --- Delete Last ---
function deleteLast() {
    const preview = document.getElementById('bracelet-preview');
    if (preview.lastElementChild && preview.lastElementChild.tagName === 'IMG') {
        const lastImg = preview.lastElementChild;
        total -= parseFloat(lastImg.dataset.price);
        count--;
        lastImg.remove();
        selectedCharms.pop();

        if (count === 0) {
            const placeholder = document.getElementById('placeholder-text');
            if (placeholder) placeholder.style.display = 'block';
        }
        updateDisplay();
    }
}

// --- Update Display ---
function updateDisplay() {
    document.getElementById('total-price').innerText = "$" + total;
    document.getElementById('charm-count').innerText = count;
    const btn = document.getElementById('add-to-cart-btn');
    btn.disabled = count < 10;
    btn.style.opacity = count < 10 ? "0.5" : "1";
}

// --- Submit to Cart ---
function goToCart() {
    const orderData = { items: selectedCharms, totalPrice: total, itemCount: count, currency: "$" };

    fetch('actions/custom_cart_add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.showErrorToast && window.showErrorToast('Your custom bracelet has been saved to the cart! ✨', 'success');
            window.location.href = "cart";
        } else {
            window.showErrorToast && window.showErrorToast('Error saving custom bracelet: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(err => {
        console.error(err);
        window.showErrorToast && window.showErrorToast('Error connecting to the server. Please try again.', 'error');
    });
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
        resultsDiv.innerHTML = '<div style="padding:15px;text-align:center;color:var(--text-light);">No products found</div>';
    } else {
        resultsDiv.innerHTML = filtered.map(p =>
            `<a href="${p.url}" class="search-item" style="text-decoration:none;color:inherit;">
                <div class="search-item-name">${p.name}</div>
                <div class="search-item-category">${p.category}</div>
            </a>`
        ).join('');
    }
    resultsDiv.style.display = 'block';
}

function toggleMenu() {
    const menu = document.getElementById('menuDropdown');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

// --- Close menus on outside click ---
document.addEventListener('click', function (event) {
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

document.addEventListener('click', function (event) {
    const dropdown = document.getElementById('favorites-dropdown') || document.getElementById('favoritesDropdown');
    const gemIcon = document.querySelector('.gem-icon');
    if (dropdown && gemIcon && !dropdown.contains(event.target) && !gemIcon.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});
