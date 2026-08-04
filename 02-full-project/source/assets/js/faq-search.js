// ═══════════════════════════════════════════════════════
//  FAQ Search - filter questions/answers in real-time
// ═══════════════════════════════════════════════════════
(function () {
    var faqSearchInput = document.getElementById('faqSearch');
    if (!faqSearchInput) return;

    var faqSections = document.querySelectorAll('.faq-content > section');
    var faqContent  = document.querySelector('.faq-content');
    var faqSidebar  = document.querySelector('.faq-sidebar');
    var quickLinks  = document.querySelector('.quick-links');
    var helpBanner  = document.querySelector('.help-banner');

    // ── No-results message ──────────────────────────────
    var noResults = document.createElement('div');
    noResults.className = 'faq-no-results';
    noResults.style.cssText = 'display:none; text-align:center; padding:60px 20px; color:var(--text-light);';
    noResults.innerHTML =
        '<div style="font-size:3rem; margin-bottom:16px;">🔍</div>' +
        '<h3 style="color:var(--primary); margin-bottom:8px; font-size:1.25rem;">No results found</h3>' +
        '<p style="margin:0; font-size:0.95rem;">Try a different keyword like <strong>shipping</strong>, <strong>returns</strong>, or <strong>customization</strong>.</p>';
    faqContent.appendChild(noResults);

    // ── Highlight style ─────────────────────────────────
    var hs = document.createElement('style');
    hs.textContent =
        '.faq-highlight {' +
        '  background: linear-gradient(135deg, rgba(255,158,187,0.35), rgba(141,212,204,0.3));' +
        '  color: var(--primary);' +
        '  padding: 1px 4px;' +
        '  border-radius: 4px;' +
        '  font-weight: 600;' +
        '}';
    document.head.appendChild(hs);

    // ── Store original content for every FAQ item ───────
    var faqItems  = document.querySelectorAll('.faq-item');
    var originals = [];

    faqItems.forEach(function (item) {
        var qEl = item.querySelector('.faq-question-text');
        var aEl = item.querySelector('.faq-answer-inner');
        originals.push({
            item:         item,
            questionEl:   qEl,
            answerEl:     aEl,
            questionHTML: qEl ? qEl.innerHTML : '',
            answerHTML:   aEl ? aEl.innerHTML : '',
            questionText: qEl ? qEl.textContent.toLowerCase() : '',
            answerText:   aEl ? aEl.textContent.toLowerCase() : ''
        });
    });

    // ── Helpers ─────────────────────────────────────────
    function escapeRegex(s) {
        return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function highlightText(html, q) {
        if (!q) return html;
        var re = new RegExp('(' + escapeRegex(q) + ')', 'gi');
        // Only replace inside text nodes (between > and <)
        return html.replace(/>([^<]+)</g, function (m, t) {
            return '>' + t.replace(re, '<span class="faq-highlight">$1</span>') + '<';
        });
    }

    // ── Debounced search ────────────────────────────────
    var timer;
    faqSearchInput.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(doSearch, 150);
    });

    function doSearch() {
        var query = faqSearchInput.value.trim().toLowerCase();

        // ── Reset when input is cleared ─────────────────
        if (!query) {
            originals.forEach(function (o) {
                o.item.style.display = '';
                o.questionEl.innerHTML = o.questionHTML;
                o.answerEl.innerHTML   = o.answerHTML;
                var t = o.item.querySelector('.faq-toggle');
                if (t) t.checked = false;
            });
            faqSections.forEach(function (s) { s.style.display = ''; });
            noResults.style.display = 'none';
            if (faqSidebar) faqSidebar.style.display = '';
            if (quickLinks) quickLinks.style.display  = '';
            if (helpBanner) helpBanner.style.display  = '';
            return;
        }

        // ── Filter FAQ items ────────────────────────────
        var total = 0;

        originals.forEach(function (o) {
            var matchQ = o.questionText.includes(query);
            var matchA = o.answerText.includes(query);

            if (matchQ || matchA) {
                o.item.style.display = '';
                o.questionEl.innerHTML = highlightText(o.questionHTML, query);
                o.answerEl.innerHTML   = highlightText(o.answerHTML, query);
                // Auto-open matching accordion
                var t = o.item.querySelector('.faq-toggle');
                if (t) t.checked = true;
                total++;
            } else {
                o.item.style.display = 'none';
                o.questionEl.innerHTML = o.questionHTML;
                o.answerEl.innerHTML   = o.answerHTML;
                var t = o.item.querySelector('.faq-toggle');
                if (t) t.checked = false;
            }
        });

        // ── Hide sections with zero visible items ───────
        faqSections.forEach(function (sec) {
            var hasVisible = false;
            sec.querySelectorAll('.faq-item').forEach(function (it) {
                if (it.style.display !== 'none') hasVisible = true;
            });
            sec.style.display = hasVisible ? '' : 'none';
        });

        // ── Toggle helper elements ──────────────────────
        noResults.style.display = (total === 0) ? '' : 'none';
        if (faqSidebar) faqSidebar.style.display = (total === 0) ? 'none' : '';
        if (quickLinks) quickLinks.style.display  = query ? 'none' : '';
        if (helpBanner) helpBanner.style.display  = (total === 0) ? 'none' : '';
    }
})();
