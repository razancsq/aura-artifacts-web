/* ═══════════════════════════════════════════════════════════
   DARK MODE - Toggle & Persistence
   Saves preference to localStorage, applies on page load
   ═══════════════════════════════════════════════════════════ */

(function() {
    'use strict';

    const STORAGE_KEY = 'aura-theme';
    const DARK = 'dark';
    const LIGHT = 'light';

    /**
     * Apply theme immediately (before DOM ready to prevent flash)
     */
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        updateToggleIcon(theme);
    }

    /**
     * Update the toggle button icon
     */
    function updateToggleIcon(theme) {
        const btn = document.getElementById('darkModeToggle');
        if (!btn) return;
        btn.innerHTML = theme === DARK ? '☀️' : '🌙';
        btn.setAttribute('title', theme === DARK ? 'Switch to Light Mode' : 'Switch to Dark Mode');
        btn.setAttribute('aria-label', theme === DARK ? 'Switch to Light Mode' : 'Switch to Dark Mode');
    }

    /**
     * Get saved theme or default to light
     */
    function getSavedTheme() {
        return localStorage.getItem(STORAGE_KEY) || LIGHT;
    }

    /**
     * Toggle between dark and light
     */
    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme') || LIGHT;
        const next = current === DARK ? LIGHT : DARK;
        applyTheme(next);
        localStorage.setItem(STORAGE_KEY, next);

        // Micro-animation on toggle
        const btn = document.getElementById('darkModeToggle');
        if (btn) {
            btn.style.transform = 'scale(1.3) rotate(360deg)';
            setTimeout(() => { btn.style.transform = ''; }, 400);
        }
    }

    // Apply saved theme instantly (prevents flash of wrong theme)
    applyTheme(getSavedTheme());

    // Wait for DOM to set up the toggle button
    document.addEventListener('DOMContentLoaded', function() {
        updateToggleIcon(getSavedTheme());

        const btn = document.getElementById('darkModeToggle');
        if (btn) {
            btn.addEventListener('click', toggleTheme);
        }
    });

    // Expose for inline onclick fallback
    window.toggleDarkMode = toggleTheme;
})();
