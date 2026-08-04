// ============================================================
// collections.js - Collections Page Scripts
// Handles: Quiz, Filter, Search, Menu
// ============================================================

'use strict';

// --- Gemstone Quiz ---
function showResult(mood) {
    const resultsContainer = document.getElementById('quiz-results-container');
    const resultName = document.getElementById('result-name');
    const resultDesc = document.getElementById('result-desc');
    const resultBtn = document.querySelector('.result-btn');

    const results = {
        happy: {
            name: 'Pink Tourmaline',
            desc: 'Your happiness radiates warmth! Pink Tourmaline amplifies your joy and spreads love around you. Tip: Wear it to keep your heart open and share your happiness with others.',
            url: 'product-detail?id=1'
        },
        excited: {
            name: 'Topaz',
            desc: "Your excitement shines bright like gold! Topaz brings abundance and positive energy. Tip: Let its golden sparkle fuel your enthusiasm and bring your dreams to life.",
            url: 'product-detail?id=2'
        },
        surprised: {
            name: 'Amethyst',
            desc: "Your sense of wonder deserves Amethyst's magic! This gem brings clarity and spiritual insight. Tip: Wear it to embrace the unexpected and find wisdom in surprises.",
            url: 'product-detail?id=3'
        },
        anxious: {
            name: 'Emerald',
            desc: "Your worries need Emerald's calming energy! This stone brings peace and grounding. Tip: Hold it when you feel anxious to reconnect with nature's tranquility.",
            url: 'product-detail?id=7'
        },
        calm: {
            name: 'Pearl',
            desc: "Your peaceful state is perfectly matched with Pearl's serenity! This gem brings inner harmony. Tip: Wear it to maintain your calm and deepen your meditation practice.",
            url: 'product-detail?id=8'
        },
        confident: {
            name: 'Blue Sapphire',
            desc: "Your confidence deserves Blue Sapphire's noble strength! This gem enhances clarity and power. Tip: Wear it to boost your confidence and trust your inner wisdom.",
            url: 'product-detail?id=4'
        },
        passionate: {
            name: 'Ruby',
            desc: "Your passion ignites like Ruby's fiery glow! This stone amplifies courage and vitality. Tip: Wear it boldly to embrace your power and pursue what you love.",
            url: 'product-detail?id=5'
        },
        elegant: {
            name: 'Diamond',
            desc: "Your elegance shines like Diamond's brilliance! This gem represents timeless grace and strength. Tip: Wear it as a reminder of your unique beauty and invaluable worth.",
            url: 'product-detail?id=9'
        }
    };

    resultName.textContent = results[mood].name;
    resultDesc.textContent = results[mood].desc;
    resultBtn.href = results[mood].url;
    resultsContainer.style.display = 'block';
    resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// --- Category Filter ---
function filterProducts(category) {
    const cards = document.querySelectorAll('.product-card');
    const buttons = document.querySelectorAll('.filter-btn');

    // Update active button
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.textContent.trim().toLowerCase() === category.toLowerCase() ||
            (category === 'all' && btn.textContent.trim().toLowerCase() === 'all')) {
            btn.classList.add('active');
        }
    });

    // Show/hide cards
    cards.forEach(card => {
        const cardCategory = card.getAttribute('data-category');
        if (category === 'all' || cardCategory === category) {
            card.style.display = '';
            card.style.animation = 'fadeIn 0.5s ease-out';
        } else {
            card.style.display = 'none';
        }
    });
}

// --- Search ---
const products = [
    { name: 'Pink Tourmaline Drop Earrings', category: 'Earrings', url: 'product-detail?id=1' },
    { name: 'Golden Radiance', category: 'Ring', url: 'product-detail?id=2' },
    { name: 'Royal Purple', category: 'Pendant', url: 'product-detail?id=3' },
    { name: 'Emerald Essence', category: 'Pendant', url: 'product-detail?id=7' },
    { name: 'Luminous Pearl', category: 'Bracelet', url: 'product-detail?id=8' },
    { name: 'Sapphire Serenity', category: 'Ring', url: 'product-detail?id=4' },
    { name: 'Passionate Fire', category: 'Anklet', url: 'product-detail?id=5' },
    { name: 'Timeless Brilliance', category: 'Pendant', url: 'product-detail?id=9' },
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

// --- Close favorites dropdown ---
document.addEventListener('click', function (event) {
    const dropdown = document.getElementById('favorites-dropdown') || document.getElementById('favoritesDropdown');
    const gemIcon = document.querySelector('.gem-icon');
    if (dropdown && gemIcon && !dropdown.contains(event.target) && !gemIcon.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});
