// ============================================================
// currency-converter.js - Advanced External API Integration
// Fetches live exchange rates from open.er-api.com (free API)
// Supports 150+ currencies with search, localStorage persistence,
// and direct product price integration
// Satisfies Milestone 4: API Integration (2 marks)
// ============================================================

(function () {
    'use strict';

    // --- Configuration ---
    const API_URL = 'https://open.er-api.com/v6/latest/USD';
    const STORAGE_KEY = 'aura_currency';
    const RATES_CACHE_KEY = 'aura_rates_cache';
    const CACHE_DURATION = 3600000; // 1 hour in ms

    // Popular currencies shown at top of list
    const POPULAR = ['USD','EUR','GBP','SAR','AED','JPY','CNY','INR','CAD','AUD'];

    // Currency metadata for display
    const CURRENCY_META = {
        USD:{s:'$',n:'US Dollar',f:'🇺🇸'},EUR:{s:'€',n:'Euro',f:'🇪🇺'},GBP:{s:'£',n:'British Pound',f:'🇬🇧'},
        SAR:{s:'ر.س',n:'Saudi Riyal',f:'🇸🇦'},AED:{s:'د.إ',n:'UAE Dirham',f:'🇦🇪'},JPY:{s:'¥',n:'Japanese Yen',f:'🇯🇵'},
        CNY:{s:'¥',n:'Chinese Yuan',f:'🇨🇳'},INR:{s:'₹',n:'Indian Rupee',f:'🇮🇳'},CAD:{s:'C$',n:'Canadian Dollar',f:'🇨🇦'},
        AUD:{s:'A$',n:'Australian Dollar',f:'🇦🇺'},CHF:{s:'Fr',n:'Swiss Franc',f:'🇨🇭'},KRW:{s:'₩',n:'South Korean Won',f:'🇰🇷'},
        TRY:{s:'₺',n:'Turkish Lira',f:'🇹🇷'},BRL:{s:'R$',n:'Brazilian Real',f:'🇧🇷'},MXN:{s:'$',n:'Mexican Peso',f:'🇲🇽'},
        SGD:{s:'S$',n:'Singapore Dollar',f:'🇸🇬'},HKD:{s:'HK$',n:'Hong Kong Dollar',f:'🇭🇰'},NOK:{s:'kr',n:'Norwegian Krone',f:'🇳🇴'},
        SEK:{s:'kr',n:'Swedish Krona',f:'🇸🇪'},DKK:{s:'kr',n:'Danish Krone',f:'🇩🇰'},PLN:{s:'zł',n:'Polish Zloty',f:'🇵🇱'},
        THB:{s:'฿',n:'Thai Baht',f:'🇹🇭'},MYR:{s:'RM',n:'Malaysian Ringgit',f:'🇲🇾'},IDR:{s:'Rp',n:'Indonesian Rupiah',f:'🇮🇩'},
        PHP:{s:'₱',n:'Philippine Peso',f:'🇵🇭'},EGP:{s:'E£',n:'Egyptian Pound',f:'🇪🇬'},PKR:{s:'₨',n:'Pakistani Rupee',f:'🇵🇰'},
        BHD:{s:'BD',n:'Bahraini Dinar',f:'🇧🇭'},KWD:{s:'KD',n:'Kuwaiti Dinar',f:'🇰🇼'},QAR:{s:'QR',n:'Qatari Riyal',f:'🇶🇦'},
        OMR:{s:'OMR',n:'Omani Rial',f:'🇴🇲'},JOD:{s:'JD',n:'Jordanian Dinar',f:'🇯🇴'},NGN:{s:'₦',n:'Nigerian Naira',f:'🇳🇬'},
        ZAR:{s:'R',n:'South African Rand',f:'🇿🇦'},RUB:{s:'₽',n:'Russian Ruble',f:'🇷🇺'},UAH:{s:'₴',n:'Ukrainian Hryvnia',f:'🇺🇦'},
        NZD:{s:'NZ$',n:'New Zealand Dollar',f:'🇳🇿'},CZK:{s:'Kč',n:'Czech Koruna',f:'🇨🇿'},HUF:{s:'Ft',n:'Hungarian Forint',f:'🇭🇺'},
        RON:{s:'lei',n:'Romanian Leu',f:'🇷🇴'},BGN:{s:'лв',n:'Bulgarian Lev',f:'🇧🇬'},HRK:{s:'kn',n:'Croatian Kuna',f:'🇭🇷'},
        ILS:{s:'₪',n:'Israeli Shekel',f:'🇮🇱'},CLP:{s:'$',n:'Chilean Peso',f:'🇨🇱'},COP:{s:'$',n:'Colombian Peso',f:'🇨🇴'},
        PEN:{s:'S/',n:'Peruvian Sol',f:'🇵🇪'},ARS:{s:'$',n:'Argentine Peso',f:'🇦🇷'},VND:{s:'₫',n:'Vietnamese Dong',f:'🇻🇳'},
        TWD:{s:'NT$',n:'Taiwan Dollar',f:'🇹🇼'},KES:{s:'KSh',n:'Kenyan Shilling',f:'🇰🇪'},GHS:{s:'₵',n:'Ghanaian Cedi',f:'🇬🇭'},
        MAD:{s:'MAD',n:'Moroccan Dirham',f:'🇲🇦'},TND:{s:'DT',n:'Tunisian Dinar',f:'🇹🇳'},LKR:{s:'Rs',n:'Sri Lankan Rupee',f:'🇱🇰'},
        BDT:{s:'৳',n:'Bangladeshi Taka',f:'🇧🇩'},MMK:{s:'K',n:'Myanmar Kyat',f:'🇲🇲'},KZT:{s:'₸',n:'Kazakhstani Tenge',f:'🇰🇿'}
    };

    let allRates = {};
    let currentCurrency = localStorage.getItem(STORAGE_KEY) || 'USD';
    let isOpen = false;

    // =====================================================
    // API: Fetch Exchange Rates
    // =====================================================
    async function fetchRates() {
        // Check cache first
        try {
            const cached = JSON.parse(localStorage.getItem(RATES_CACHE_KEY) || '{}');
            if (cached.rates && cached.ts && (Date.now() - cached.ts < CACHE_DURATION)) {
                allRates = cached.rates;
                console.log('[Currency API] Loaded from cache (' + Object.keys(allRates).length + ' currencies)');
                return allRates;
            }
        } catch(e) { /* cache miss */ }

        console.log('[Currency API] Fetching live rates from:', API_URL);

        try {
            const res = await fetch(API_URL);
            if (!res.ok) throw new Error('HTTP ' + res.status);

            const data = await res.json();
            if (data.result !== 'success' || !data.rates) throw new Error('Invalid response');

            allRates = data.rates;

            // Cache the rates
            localStorage.setItem(RATES_CACHE_KEY, JSON.stringify({ rates: allRates, ts: Date.now() }));

            console.log('%c✅ Currency API: ' + Object.keys(allRates).length + ' currencies loaded', 'color:#27ae60;font-weight:bold');
            return allRates;

        } catch (err) {
            console.error('[Currency API] Error:', err.message);

            // Fallback rates
            allRates = {USD:1,EUR:0.85,GBP:0.74,SAR:3.75,AED:3.67,JPY:157,CNY:7.25,INR:83.5,CAD:1.36,AUD:1.53};
            console.warn('[Currency API] Using fallback rates');
            return allRates;
        }
    }

    // =====================================================
    // Price Conversion
    // =====================================================
    function getSymbol(code) {
        return (CURRENCY_META[code] && CURRENCY_META[code].s) || code + ' ';
    }

    function convertAndFormat(usdAmount, toCode) {
        const rate = allRates[toCode] || 1;
        const converted = (parseFloat(usdAmount) * rate);
        // For currencies with very large values (like JPY, KRW), skip decimals
        const decimals = (converted > 1000 && ['JPY','KRW','VND','IDR','CLP','COP','HUF','PKR'].includes(toCode)) ? 0 : 2;
        return getSymbol(toCode) + converted.toFixed(decimals);
    }

    // =====================================================
    // DOM: Tag & Update Prices
    // =====================================================
    function tagAllPrices() {
        // Match elements containing price patterns like $450.00 or $2,500
        const selectors = '.card-price, .product-price, .price, .price-tag';
        document.querySelectorAll(selectors).forEach(el => {
            if (el.hasAttribute('data-usd')) return;
            const txt = el.textContent.trim();
            const m = txt.match(/^\$?([\d,]+\.?\d{0,2})$/);
            if (m) {
                el.setAttribute('data-usd', m[1].replace(/,/g, ''));
            }
        });
        // Also scan for inline $XXX patterns
        document.querySelectorAll('span, div, p, h3, h4, td').forEach(el => {
            if (el.hasAttribute('data-usd') || el.children.length > 0) return;
            const txt = el.textContent.trim();
            const m = txt.match(/^\$([\d,]+\.?\d{0,2})$/);
            if (m) el.setAttribute('data-usd', m[1].replace(/,/g, ''));
        });

        console.log('[Currency] Tagged', document.querySelectorAll('[data-usd]').length, 'price elements');
    }

    function updatePrices(toCode) {
        currentCurrency = toCode;
        localStorage.setItem(STORAGE_KEY, toCode);

        document.querySelectorAll('[data-usd]').forEach(el => {
            const usd = parseFloat(el.getAttribute('data-usd'));
            if (isNaN(usd)) return;

            const formatted = convertAndFormat(usd, toCode);

            // Animate
            el.style.transition = 'opacity 0.15s, transform 0.15s';
            el.style.opacity = '0.4';
            el.style.transform = 'scale(0.96)';

            setTimeout(() => {
                if (toCode === 'USD') {
                    el.textContent = '$' + usd.toFixed(2);
                } else {
                    el.innerHTML = formatted + '<small style="display:block;font-size:0.65em;opacity:0.6;font-weight:400;margin-top:2px;">≈ $' + usd.toFixed(2) + ' USD</small>';
                }
                el.style.opacity = '1';
                el.style.transform = 'scale(1)';
            }, 120);
        });
    }

    // =====================================================
    // UI: Build Currency Dropdown in Navbar Area
    // =====================================================
    function buildUI() {
        // Create the trigger button (goes in bottom-left as a compact bar)
        const wrapper = document.createElement('div');
        wrapper.id = 'currencyBox';
        wrapper.innerHTML = `
            <button id="currencyToggle" aria-label="Change Currency">
                <span class="curr-flag">${(CURRENCY_META[currentCurrency]||{}).f||'💱'}</span>
                <span class="curr-code" id="currCodeLabel">${currentCurrency}</span>
                <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            </button>
            <div id="currencyDropdown" class="curr-dropdown">
                <div class="curr-search-box">
                    <input type="text" id="currencySearch" placeholder="Search currency..." autocomplete="off" />
                </div>
                <div class="curr-list" id="currencyList"></div>
            </div>
        `;

        // Inject styles
        const style = document.createElement('style');
        style.textContent = `
            #currencyBox{position:fixed;bottom:24px;left:24px;z-index:9998;font-family:'Poppins',sans-serif}
            #currencyToggle{display:flex;align-items:center;gap:8px;padding:10px 16px;background:rgba(255,255,255,0.97);
                backdrop-filter:blur(12px);border:1.5px solid #D4AF37;border-radius:12px;cursor:pointer;
                font-size:0.88rem;font-weight:600;color:#2d3e50;box-shadow:0 4px 16px rgba(0,0,0,0.08);
                transition:all 0.25s ease;font-family:inherit}
            #currencyToggle:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(212,175,55,0.2);border-color:#c4a030}
            .curr-flag{font-size:1.15rem}
            .curr-dropdown{display:none;position:absolute;bottom:52px;left:0;width:320px;max-height:420px;
                background:#fff;border:1.5px solid #D4AF37;border-radius:16px;box-shadow:0 16px 48px rgba(0,0,0,0.15);
                overflow:hidden;animation:currSlideUp 0.25s ease}
            .curr-dropdown.open{display:block}
            @keyframes currSlideUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
            .curr-search-box{padding:12px 14px;border-bottom:1px solid #f0e8d8}
            #currencySearch{width:100%;padding:10px 14px;border:1.5px solid #e8e0d0;border-radius:10px;font-size:0.88rem;
                font-family:inherit;outline:none;transition:border 0.2s}
            #currencySearch:focus{border-color:#D4AF37}
            .curr-list{max-height:340px;overflow-y:auto;padding:6px 0}
            .curr-list::-webkit-scrollbar{width:5px}
            .curr-list::-webkit-scrollbar-thumb{background:#D4AF37;border-radius:10px}
            .curr-group-label{padding:6px 16px;font-size:0.68rem;text-transform:uppercase;letter-spacing:1.5px;
                color:#b0a080;font-weight:700;background:#fdfbf7}
            .curr-item{display:flex;align-items:center;gap:10px;padding:10px 16px;cursor:pointer;transition:all 0.15s;
                font-size:0.88rem;color:#2d3e50;border-left:3px solid transparent}
            .curr-item:hover{background:#fef9f0;border-left-color:#D4AF37;padding-left:20px}
            .curr-item.active{background:#fef4e8;border-left-color:#D4AF37;font-weight:700}
            .curr-item .ci-flag{font-size:1.1rem;width:22px;text-align:center}
            .curr-item .ci-code{font-weight:600;min-width:36px}
            .curr-item .ci-name{color:#888;font-size:0.82rem;flex:1}
            .curr-item .ci-rate{color:#D4AF37;font-size:0.78rem;font-weight:600}
            .curr-no-results{padding:24px;text-align:center;color:#aaa;font-size:0.88rem}
        `;
        document.head.appendChild(style);
        document.body.appendChild(wrapper);

        // Events
        document.getElementById('currencyToggle').addEventListener('click', (e) => {
            e.stopPropagation();
            isOpen = !isOpen;
            document.getElementById('currencyDropdown').classList.toggle('open', isOpen);
            if (isOpen) {
                renderList('');
                setTimeout(() => document.getElementById('currencySearch').focus(), 100);
            }
        });

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                isOpen = false;
                document.getElementById('currencyDropdown').classList.remove('open');
            }
        });

        // Search with debounce
        let searchTimer;
        document.getElementById('currencySearch').addEventListener('input', function() {
            clearTimeout(searchTimer);
            const q = this.value;
            searchTimer = setTimeout(() => renderList(q), 150);
        });
    }

    function renderList(query) {
        const listEl = document.getElementById('currencyList');
        const q = query.toLowerCase().trim();
        let html = '';

        // Build list of currencies with their info
        const allCodes = Object.keys(allRates).filter(c => c !== 'USD');
        allCodes.unshift('USD'); // USD always first

        // Filter
        const filtered = allCodes.filter(code => {
            if (!q) return true;
            const meta = CURRENCY_META[code];
            const name = meta ? meta.n.toLowerCase() : '';
            return code.toLowerCase().includes(q) || name.includes(q);
        });

        if (filtered.length === 0) {
            listEl.innerHTML = '<div class="curr-no-results">No currencies found</div>';
            return;
        }

        // Separate popular from others
        const popularFiltered = filtered.filter(c => POPULAR.includes(c));
        const othersFiltered = filtered.filter(c => !POPULAR.includes(c));

        if (popularFiltered.length > 0 && !q) {
            html += '<div class="curr-group-label">⭐ Popular</div>';
            popularFiltered.forEach(code => { html += renderItem(code); });
        }

        if (othersFiltered.length > 0 || q) {
            if (!q) html += '<div class="curr-group-label">🌍 All Currencies</div>';
            const list = q ? filtered : othersFiltered;
            list.sort((a,b) => a.localeCompare(b));
            list.forEach(code => { html += renderItem(code); });
        }

        listEl.innerHTML = html;

        // Click events
        listEl.querySelectorAll('.curr-item').forEach(item => {
            item.addEventListener('click', () => {
                const code = item.dataset.code;
                selectCurrency(code);
            });
        });
    }

    function renderItem(code) {
        const meta = CURRENCY_META[code] || {};
        const flag = meta.f || '🏳️';
        const name = meta.n || code;
        const rate = allRates[code] ? allRates[code].toFixed(4) : '-';
        const isActive = code === currentCurrency ? ' active' : '';
        return `<div class="curr-item${isActive}" data-code="${code}">
            <span class="ci-flag">${flag}</span>
            <span class="ci-code">${code}</span>
            <span class="ci-name">${name}</span>
            <span class="ci-rate">${rate}</span>
        </div>`;
    }

    function selectCurrency(code) {
        currentCurrency = code;
        const meta = CURRENCY_META[code] || {};

        // Update button label
        document.getElementById('currCodeLabel').textContent = code;
        const flagEl = document.querySelector('.curr-flag');
        if (flagEl) flagEl.textContent = meta.f || '💱';

        // Close dropdown
        isOpen = false;
        document.getElementById('currencyDropdown').classList.remove('open');

        // Update all prices
        updatePrices(code);

        console.log(`[Currency] Switched to ${code} (rate: ${allRates[code]})`);
    }

    // =====================================================
    // Initialize
    // =====================================================
    document.addEventListener('DOMContentLoaded', async () => {
        console.log('[Currency API] Initializing advanced currency converter...');

        buildUI();
        tagAllPrices();
        await fetchRates();

        // Apply saved currency if not USD
        if (currentCurrency !== 'USD' && allRates[currentCurrency]) {
            selectCurrency(currentCurrency);
        }

        // Update button to show loaded state
        const btn = document.getElementById('currencyToggle');
        if (btn) btn.title = Object.keys(allRates).length + ' currencies available (Live)';
    });

    // Expose globally
    window.currencyConverter = {
        convert: convertAndFormat,
        updateAll: updatePrices,
        getRates: () => allRates,
        getCurrent: () => currentCurrency
    };

})();
