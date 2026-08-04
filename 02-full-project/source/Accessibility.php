<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accessibility - Aura Artifacts</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Playfair+Display:wght@400;600&family=Poppins:wght@300;400;500&family=Lato:wght@400;600;700&display=swap" rel="stylesheet">
    <style>

        /* ===========================================================
           ACCESSIBILITY PAGE - Page-Specific Styles
        =========================================================== */

        /* Page Header */
        .page-header {
            position: relative;
            padding: 100px 0 80px;
            text-align: center;
            overflow: hidden;
            min-height: 420px;
            display: flex;
            align-items: center;
        }

        /* Background image layer */
        .page-header-bg {
            position: absolute;
            inset: 0;
            background-image: url('assets/images/ACCSEBELTY.jpg');
            background-size: cover;
            background-position: center center;
            filter: brightness(0.45) saturate(0.8);
            z-index: 0;
        }

        /* Gradient overlay on top of image */
        .page-header-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                135deg,
                rgba(141, 212, 204, 0.55) 0%,
                rgba(255, 158, 187, 0.45) 50%,
                rgba(45, 62, 80, 0.6) 100%
            );
            z-index: 1;
        }

        /* Shimmer sweep effect */
        .page-header-overlay::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                105deg,
                transparent 30%,
                rgba(255, 255, 255, 0.06) 50%,
                transparent 70%
            );
            animation: headerShimmer 4s ease-in-out infinite;
        }

        @keyframes headerShimmer {
            0%   { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Content sits above everything */
        .page-header-content {
            position: relative;
            z-index: 2;
            width: 100%;
        }

        .page-header h1 {
            color: #FFFFFF;
            text-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
            margin-bottom: var(--space-sm);
        }

        .page-header .highlight-word {
            background: linear-gradient(135deg, #FFD1E0, var(--turquoise-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-header p {
            font-size: 1.125rem;
            max-width: 600px;
            margin: 0 auto;
            color: rgba(255, 255, 255, 0.92);
            text-shadow: 0 1px 8px rgba(0, 0, 0, 0.2);
        }

        .page-header .badge {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #FFFFFF;
            box-shadow: none;
        }

        /* Commitment Banner */
        .commitment-banner {
            background: var(--primary);
            color: var(--pure);
            padding: var(--space-lg) 0;
            text-align: center;
        }

        .commitment-banner p {
            color: rgba(255,255,255,0.9);
            font-size: 1rem;
            max-width: 700px;
            margin: 0 auto;
        }

        .commitment-banner strong {
            color: var(--turquoise-light);
        }

        /* Features Grid */
        .access-features {
            padding: var(--space-xxl) 0;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--space-lg);
            margin-top: var(--space-xl);
        }

        .access-feature-card {
            background: var(--pure);
            border: 1px solid rgba(0,0,0,0.07);
            border-radius: 16px;
            padding: var(--space-lg);
            transition: var(--transition-smooth);
            position: relative;
            overflow: hidden;
        }

        .access-feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--turquoise), var(--accent));
            transform: scaleX(0);
            transition: transform 0.4s ease;
            transform-origin: left;
        }

        .access-feature-card:hover::before {
            transform: scaleX(1);
        }

        .access-feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 35px rgba(141, 212, 204, 0.2);
            border-color: var(--turquoise);
        }

        .access-icon {
            font-size: 2.75rem;
            margin-bottom: var(--space-md);
            display: block;
        }

        .access-feature-card h3 {
            font-size: 1.25rem;
            margin-bottom: var(--space-sm);
            color: var(--primary);
            font-family: var(--font-subheading);
        }

        .access-feature-card p {
            font-size: 0.9375rem;
            margin-bottom: 0;
        }

        /* How-To Section */
        .how-to-section {
            background: #FAFAFA;
            padding: var(--space-xxl) 0;
        }

        .how-to-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-xl);
            margin-top: var(--space-xl);
        }

        .how-to-card {
            background: var(--pure);
            border-radius: 16px;
            padding: var(--space-xl);
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .how-to-card h3 {
            font-size: 1.375rem;
            color: var(--primary);
            margin-bottom: var(--space-md);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .how-to-card h3 .htc-icon {
            font-size: 1.5rem;
        }

        .how-to-steps {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: var(--space-sm);
        }

        .how-to-steps li {
            display: flex;
            align-items: flex-start;
            gap: var(--space-sm);
            font-size: 0.9375rem;
            color: var(--text-light);
            line-height: 1.7;
        }

        .step-number {
            min-width: 28px;
            height: 28px;
            background: linear-gradient(135deg, var(--accent), var(--turquoise));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            font-family: var(--font-button);
            flex-shrink: 0;
            margin-top: 2px;
        }

        /* Keyboard Shortcuts Table */
        .keyboard-section {
            padding: var(--space-xxl) 0;
        }

        .shortcuts-table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.08);
            margin-top: var(--space-xl);
        }

        .shortcuts-table {
            width: 100%;
            border-collapse: collapse;
            font-family: var(--font-body);
        }

        .shortcuts-table thead {
            background: linear-gradient(135deg, var(--primary), #1a2634);
            color: var(--pure);
        }

        .shortcuts-table th {
            padding: var(--space-md) var(--space-lg);
            text-align: left;
            font-family: var(--font-button);
            font-size: 0.875rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .shortcuts-table tbody tr {
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: background 0.2s ease;
        }

        .shortcuts-table tbody tr:hover {
            background: var(--cream);
        }

        .shortcuts-table tbody tr:last-child {
            border-bottom: none;
        }

        .shortcuts-table td {
            padding: var(--space-md) var(--space-lg);
            font-size: 0.9375rem;
            color: var(--text-light);
            vertical-align: middle;
        }

        .shortcuts-table td:first-child {
            color: var(--text-dark);
            font-weight: 500;
        }

        kbd {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            background: #F3F4F6;
            border: 1px solid #D1D5DB;
            border-bottom: 3px solid #9CA3AF;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.8125rem;
            color: var(--text-dark);
            font-weight: 600;
            margin: 2px;
        }

        /* Preferences Panel */
        .preferences-section {
            background: #FAFAFA;
            padding: var(--space-xxl) 0;
        }

        .preferences-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-lg);
            margin-top: var(--space-xl);
        }

        .pref-card {
            background: var(--pure);
            border-radius: 16px;
            padding: var(--space-lg);
            border: 1px solid rgba(0,0,0,0.07);
            display: flex;
            align-items: flex-start;
            gap: var(--space-md);
        }

        .pref-icon-wrap {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--secondary), var(--turquoise-light));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .pref-text h4 {
            color: var(--primary);
            margin-bottom: 6px;
            font-size: 1rem;
        }

        .pref-text p {
            font-size: 0.875rem;
            margin-bottom: var(--space-sm);
        }

        .pref-badge {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(141, 212, 204, 0.2);
            color: #2a7a72;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            font-family: var(--font-button);
            letter-spacing: 0.5px;
        }

        /* Conformance Section */
        .conformance-section {
            padding: var(--space-xxl) 0;
        }

        .conformance-card {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--turquoise-light) 100%);
            border-radius: 20px;
            padding: var(--space-xl);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-xl);
            align-items: center;
        }

        .conformance-text h2 {
            margin-bottom: var(--space-md);
        }

        .conformance-text p {
            font-size: 1rem;
            line-height: 1.8;
        }

        .conformance-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .conformance-list li {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            font-size: 0.9375rem;
            color: var(--text-dark);
            font-weight: 500;
        }

        .check-circle {
            width: 28px;
            height: 28px;
            background: var(--turquoise);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        /* Feedback Section */
        .feedback-section {
            padding: var(--space-xl) 0 var(--space-xxl);
            text-align: center;
        }

        .feedback-card {
            background: var(--primary);
            border-radius: 20px;
            padding: var(--space-xl);
            color: var(--pure);
            max-width: 700px;
            margin: 0 auto;
        }

        .feedback-card h2 {
            color: var(--pure);
            margin-bottom: var(--space-sm);
        }

        .feedback-card p {
            color: rgba(255,255,255,0.8);
            margin-bottom: var(--space-lg);
        }

        /* Responsive */
        @media (max-width: 900px) {
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .how-to-grid {
                grid-template-columns: 1fr;
            }

            .conformance-card {
                grid-template-columns: 1fr;
            }

            .preferences-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .features-grid {
                grid-template-columns: 1fr;
            }

            .shortcuts-table th,
            .shortcuts-table td {
                padding: var(--space-sm) var(--space-md);
            }
        }

    </style>
</head>
<body>

    <!-- Skip Link -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

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

    <!-- Page Header -->
    <header class="page-header" role="banner">
        <div class="page-header-bg" aria-hidden="true"></div>
        <div class="page-header-overlay" aria-hidden="true"></div>
        <div class="page-header-content">
            <div class="container">
                <span class="badge mb-md">Inclusive Design</span>
                <h1>Accessibility at <span class="highlight-word">Aura Artifacts</span></h1>
                <p>We believe everyone deserves a beautiful, seamless shopping experience - regardless of ability or assistive technology.</p>
            </div>
        </div>
    </header>

    <!-- Commitment Banner -->
    <div class="commitment-banner" role="note" aria-label="Accessibility commitment">
        <div class="container">
            <p>Aura Artifacts is committed to <strong>WCAG 2.1 Level AA</strong> compliance. Our goal is to ensure all users can browse, customize, and purchase with full ease and independence.</p>
        </div>
    </div>

    <!-- Main Content -->
    <main id="main-content">

        <!-- Accessibility Features -->
        <section class="access-features" aria-labelledby="features-title">
            <div class="container">
                <div class="section-header">
                    <span class="badge mb-md">What We Offer</span>
                    <h2 id="features-title" class="section-title">Accessibility Features</h2>
                    <p class="section-subtitle">Built into every page from the ground up</p>
                </div>

                <div class="features-grid" role="list">

                    <div class="access-feature-card" role="listitem">
                        <span class="access-icon" aria-hidden="true">⌨️</span>
                        <h3>Full Keyboard Navigation</h3>
                        <p>Every interactive element on our site - buttons, links, forms, and menus - is fully accessible using only a keyboard. Tab through the page with visible focus indicators at all times.</p>
                    </div>

                    <div class="access-feature-card" role="listitem">
                        <span class="access-icon" aria-hidden="true">🔊</span>
                        <h3>Screen Reader Support</h3>
                        <p>All images include descriptive alt text. ARIA labels and roles are applied throughout the site. Our pages are structured with semantic HTML for a logical reading order.</p>
                    </div>

                    <div class="access-feature-card" role="listitem">
                        <span class="access-icon" aria-hidden="true">🎨</span>
                        <h3>High Contrast Mode</h3>
                        <p>Our site automatically adapts to your system's high contrast preferences. You can also enable high contrast manually in your browser or operating system settings.</p>
                    </div>

                    <div class="access-feature-card" role="listitem">
                        <span class="access-icon" aria-hidden="true">🚫</span>
                        <h3>Reduced Motion</h3>
                        <p>All animations and transitions are automatically disabled for users who have enabled the "Reduce Motion" setting in their operating system - no configuration needed on our site.</p>
                    </div>

                    <div class="access-feature-card" role="listitem">
                        <span class="access-icon" aria-hidden="true">📱</span>
                        <h3>Responsive & Touch-Friendly</h3>
                        <p>The entire site is fully responsive and optimized for touch devices. All tap targets meet minimum size requirements to support users with motor disabilities.</p>
                    </div>

                    <div class="access-feature-card" role="listitem">
                        <span class="access-icon" aria-hidden="true">🔗</span>
                        <h3>Skip Navigation Link</h3>
                        <p>A "Skip to main content" link is available at the top of every page, allowing keyboard and screen reader users to bypass repeated navigation and jump directly to the content.</p>
                    </div>

                </div>
            </div>
        </section>

        <!-- How To Use -->
        <section class="how-to-section" aria-labelledby="howto-title">
            <div class="container">
                <div class="section-header">
                    <span class="badge mb-md">Getting Started</span>
                    <h2 id="howto-title" class="section-title">How To Use Assistive Features</h2>
                    <p class="section-subtitle">Step-by-step guidance for common accessibility tools</p>
                </div>

                <div class="how-to-grid">

                    <div class="how-to-card">
                        <h3>
                            <span class="htc-icon" aria-hidden="true">🔊</span>
                            Using a Screen Reader
                        </h3>
                        <ol class="how-to-steps" aria-label="Screen reader steps">
                            <li>
                                <span class="step-number" aria-hidden="true">1</span>
                                Enable your screen reader (NVDA, JAWS, VoiceOver, or TalkBack)
                            </li>
                            <li>
                                <span class="step-number" aria-hidden="true">2</span>
                                Press <kbd>Tab</kbd> to activate the Skip Link at the top of the page
                            </li>
                            <li>
                                <span class="step-number" aria-hidden="true">3</span>
                                Navigate headings using <kbd>H</kbd> to jump between sections
                            </li>
                            <li>
                                <span class="step-number" aria-hidden="true">4</span>
                                All buttons and form fields are labelled for clear announcements
                            </li>
                        </ol>
                    </div>

                    <div class="how-to-card">
                        <h3>
                            <span class="htc-icon" aria-hidden="true">🔍</span>
                            Adjusting Text Size
                        </h3>
                        <ol class="how-to-steps" aria-label="Text size steps">
                            <li>
                                <span class="step-number" aria-hidden="true">1</span>
                                In your browser, press <kbd>Ctrl</kbd> + <kbd>+</kbd> to zoom in
                            </li>
                            <li>
                                <span class="step-number" aria-hidden="true">2</span>
                                Press <kbd>Ctrl</kbd> + <kbd>-</kbd> to zoom out
                            </li>
                            <li>
                                <span class="step-number" aria-hidden="true">3</span>
                                Our layout adapts at all zoom levels without horizontal scrolling
                            </li>
                            <li>
                                <span class="step-number" aria-hidden="true">4</span>
                                All text can be resized up to 200% without loss of content
                            </li>
                        </ol>
                    </div>

                    <div class="how-to-card">
                        <h3>
                            <span class="htc-icon" aria-hidden="true">🎨</span>
                            Enabling High Contrast
                        </h3>
                        <ol class="how-to-steps" aria-label="High contrast steps">
                            <li>
                                <span class="step-number" aria-hidden="true">1</span>
                                <strong>Windows:</strong> Settings → Accessibility → Contrast themes
                            </li>
                            <li>
                                <span class="step-number" aria-hidden="true">2</span>
                                <strong>Mac:</strong> System Settings → Accessibility → Display → Increase Contrast
                            </li>
                            <li>
                                <span class="step-number" aria-hidden="true">3</span>
                                Our site will automatically apply high-contrast adjustments
                            </li>
                        </ol>
                    </div>

                    <div class="how-to-card">
                        <h3>
                            <span class="htc-icon" aria-hidden="true">🚫</span>
                            Reducing Motion
                        </h3>
                        <ol class="how-to-steps" aria-label="Reduce motion steps">
                            <li>
                                <span class="step-number" aria-hidden="true">1</span>
                                <strong>Windows:</strong> Settings → Accessibility → Visual effects → Animation effects off
                            </li>
                            <li>
                                <span class="step-number" aria-hidden="true">2</span>
                                <strong>Mac:</strong> System Settings → Accessibility → Display → Reduce Motion
                            </li>
                            <li>
                                <span class="step-number" aria-hidden="true">3</span>
                                All animations on our site will stop immediately
                            </li>
                        </ol>
                    </div>

                </div>
            </div>
        </section>

        <!-- Keyboard Shortcuts -->
        <section class="keyboard-section" aria-labelledby="keyboard-title">
            <div class="container">
                <div class="section-header">
                    <span class="badge mb-md">Keyboard Access</span>
                    <h2 id="keyboard-title" class="section-title">Keyboard Shortcuts</h2>
                    <p class="section-subtitle">Navigate the entire site without a mouse</p>
                </div>

                <div class="shortcuts-table-wrapper" role="region" aria-label="Keyboard shortcuts table">
                    <table class="shortcuts-table" aria-label="Available keyboard shortcuts">
                        <thead>
                            <tr>
                                <th scope="col">Action</th>
                                <th scope="col">Keys</th>
                                <th scope="col">Where It Works</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Move to next element</td>
                                <td><kbd>Tab</kbd></td>
                                <td>Entire site</td>
                            </tr>
                            <tr>
                                <td>Move to previous element</td>
                                <td><kbd>Shift</kbd> + <kbd>Tab</kbd></td>
                                <td>Entire site</td>
                            </tr>
                            <tr>
                                <td>Activate button or link</td>
                                <td><kbd>Enter</kbd></td>
                                <td>Entire site</td>
                            </tr>
                            <tr>
                                <td>Skip to main content</td>
                                <td><kbd>Tab</kbd> then <kbd>Enter</kbd> on skip link</td>
                                <td>Top of every page</td>
                            </tr>
                            <tr>
                                <td>Open / close FAQ answer</td>
                                <td><kbd>Space</kbd> or <kbd>Enter</kbd></td>
                                <td>Help & FAQ page</td>
                            </tr>
                            <tr>
                                <td>Navigate dropdown options</td>
                                <td><kbd>↑</kbd> / <kbd>↓</kbd> Arrow keys</td>
                                <td>Select menus</td>
                            </tr>
                            <tr>
                                <td>Zoom in</td>
                                <td><kbd>Ctrl</kbd> + <kbd>+</kbd></td>
                                <td>Browser</td>
                            </tr>
                            <tr>
                                <td>Zoom out</td>
                                <td><kbd>Ctrl</kbd> + <kbd>-</kbd></td>
                                <td>Browser</td>
                            </tr>
                            <tr>
                                <td>Reset zoom</td>
                                <td><kbd>Ctrl</kbd> + <kbd>0</kbd></td>
                                <td>Browser</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Browser Preferences -->
        <section class="preferences-section" aria-labelledby="prefs-title">
            <div class="container">
                <div class="section-header">
                    <span class="badge mb-md">Supported Preferences</span>
                    <h2 id="prefs-title" class="section-title">System Preferences We Respect</h2>
                    <p class="section-subtitle">Our site listens to your device settings automatically</p>
                </div>

                <div class="preferences-grid" role="list">

                    <div class="pref-card" role="listitem">
                        <div class="pref-icon-wrap" aria-hidden="true">🚫</div>
                        <div class="pref-text">
                            <h4>Reduced Motion</h4>
                            <p>Disables all animations and transitions sitewide when your OS has "Reduce Motion" enabled.</p>
                            <span class="pref-badge">prefers-reduced-motion</span>
                        </div>
                    </div>

                    <div class="pref-card" role="listitem">
                        <div class="pref-icon-wrap" aria-hidden="true">🌓</div>
                        <div class="pref-text">
                            <h4>High Contrast</h4>
                            <p>Adjusts text and accent colors to meet higher contrast ratios when system contrast is elevated.</p>
                            <span class="pref-badge">prefers-contrast: high</span>
                        </div>
                    </div>

                    <div class="pref-card" role="listitem">
                        <div class="pref-icon-wrap" aria-hidden="true">📱</div>
                        <div class="pref-text">
                            <h4>Responsive Layout</h4>
                            <p>The layout adapts to any screen size - from large monitors to small mobile screens - without losing content or functionality.</p>
                            <span class="pref-badge">All Screen Sizes</span>
                        </div>
                    </div>

                    <div class="pref-card" role="listitem">
                        <div class="pref-icon-wrap" aria-hidden="true">🔠</div>
                        <div class="pref-text">
                            <h4>Text Resizing</h4>
                            <p>All text is set using relative units (rem/em) so it scales correctly when you adjust browser or OS font size settings.</p>
                            <span class="pref-badge">rem / em Units</span>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Conformance -->
        <section class="conformance-section" aria-labelledby="conformance-title">
            <div class="container">
                <div class="conformance-card" role="region" aria-labelledby="conformance-title">
                    <div class="conformance-text">
                        <span class="badge mb-md">Our Standards</span>
                        <h2 id="conformance-title">Accessibility Conformance</h2>
                        <p>Aura Artifacts targets <strong>WCAG 2.1 Level AA</strong> conformance. We regularly review our pages for accessibility issues and work to fix them promptly.</p>
                    </div>
                    <div>
                        <ul class="conformance-list" aria-label="Accessibility standards met">
                            <li>
                                <span class="check-circle" aria-hidden="true">✓</span>
                                Semantic HTML5 structure
                            </li>
                            <li>
                                <span class="check-circle" aria-hidden="true">✓</span>
                                ARIA roles and labels on all interactive elements
                            </li>
                            <li>
                                <span class="check-circle" aria-hidden="true">✓</span>
                                Alt text on all images
                            </li>
                            <li>
                                <span class="check-circle" aria-hidden="true">✓</span>
                                Visible focus indicators (keyboard)
                            </li>
                            <li>
                                <span class="check-circle" aria-hidden="true">✓</span>
                                Color contrast ratio ≥ 4.5:1
                            </li>
                            <li>
                                <span class="check-circle" aria-hidden="true">✓</span>
                                Forms with proper labels
                            </li>
                            <li>
                                <span class="check-circle" aria-hidden="true">✓</span>
                                Logical heading hierarchy (H1 → H2 → H3)
                            </li>
                            <li>
                                <span class="check-circle" aria-hidden="true">✓</span>
                                Skip navigation link
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feedback -->
        <section class="feedback-section" aria-labelledby="feedback-title">
            <div class="container">
                <div class="feedback-card">
                    <h2 id="feedback-title">Found an Accessibility Issue?</h2>
                    <p>We're always improving. If you encounter any barriers while using our site, please let us know and we'll address it as a priority.</p>
                    <a href="contact" class="btn-gemstone" style="display: inline-block; text-decoration: none;">
                        Report an Issue
                    </a>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="footer" role="contentinfo">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Aura Artifacts</h3>
                    <p>Handcrafted gemstone bracelets designed with intention and love.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <a href="collections">Collections</a><br>
                    <a href="BraceletCustomization">Customize</a><br>
                    <a href="about">About Us</a><br>
                    <a href="contact">Contact</a>
                </div>
                <div class="footer-section">
                    <h3>Support</h3>
                    <a href="help">Help & FAQ</a><br>
                    <a href="Accessibility" aria-current="page">Accessibility</a><br>
                    <a href="contact">Contact Us</a><br>
                    <a href="admin/admin-login">Admin Portal</a>
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