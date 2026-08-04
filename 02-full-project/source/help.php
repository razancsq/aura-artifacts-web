<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & FAQ - Aura Artifacts</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Playfair+Display:wght@400;600&family=Poppins:wght@300;400;500&family=Lato:wght@400;600;700&display=swap" rel="stylesheet">
    <style>

        /* ===========================================================
           HELP & FAQ PAGE - Page-Specific Styles
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

        .page-header-bg {
            position: absolute;
            inset: 0;
            background-image: url('assets/images/HELP.jpg');
            background-size: cover;
            background-position: center 60%;
            filter: brightness(0.45) saturate(0.8);
            z-index: 0;
        }

        .page-header-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                135deg,
                rgba(255, 158, 187, 0.5) 0%,
                rgba(141, 212, 204, 0.45) 50%,
                rgba(45, 62, 80, 0.6) 100%
            );
            z-index: 1;
        }

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
            max-width: 550px;
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

        /* Search Bar */
        .faq-search-wrapper {
            background: var(--pure);
            padding: var(--space-lg) 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .faq-search {
            display: flex;
            align-items: center;
            max-width: 600px;
            margin: 0 auto;
            background: #FAFAFA;
            border: 2px solid rgba(255, 158, 187, 0.3);
            border-radius: 50px;
            padding: 12px 24px;
            gap: var(--space-sm);
            transition: var(--transition-fast);
        }

        .faq-search:focus-within {
            border-color: var(--accent);
            background: var(--pure);
            box-shadow: 0 4px 20px rgba(255, 158, 187, 0.2);
        }

        .faq-search-icon {
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .faq-search input {
            flex: 1;
            border: none;
            background: transparent;
            font-family: var(--font-body);
            font-size: 1rem;
            color: var(--text-dark);
            outline: none;
        }

        .faq-search input::placeholder {
            color: var(--text-light);
        }

        /* Layout: sidebar + content */
        .faq-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: var(--space-xl);
            padding: var(--space-xl) 0 var(--space-xxl);
            align-items: start;
        }

        /* category Sidebar */
        .faq-sidebar {
            position: sticky;
            top: 100px;
        }

        .faq-sidebar h3 {
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--text-light);
            margin-bottom: var(--space-md);
            font-family: var(--font-button);
            font-weight: 600;
        }

        .faq-category-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .faq-category-list a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 10px;
            font-family: var(--font-body);
            font-size: 0.9375rem;
            color: var(--text-dark);
            transition: var(--transition-fast);
            border: 1px solid transparent;
        }

        .faq-category-list a:hover,
        .faq-category-list a.active {
            background: var(--secondary);
            color: var(--accent);
            border-color: rgba(255, 158, 187, 0.2);
            transform: translateX(4px);
        }

        .faq-category-list .cat-icon {
            font-size: 1rem;
            color: var(--accent);
            width: 22px;
            text-align: center;
            flex-shrink: 0;
            font-style: normal;
        }

        /* FAQ Main Content */
        .faq-content {
            display: flex;
            flex-direction: column;
            gap: var(--space-xl);
        }

        /* category Section */
        .faq-section-title {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            margin-bottom: var(--space-lg);
            padding-bottom: var(--space-sm);
            border-bottom: 2px solid var(--secondary);
        }

        .faq-section-title .section-icon {
            font-size: 1.1rem;
            color: var(--accent);
            width: 38px;
            height: 38px;
            background: var(--secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1px solid rgba(255, 158, 187, 0.3);
        }

        .faq-section-title h2 {
            font-size: 1.75rem;
            margin-bottom: 0;
            color: var(--primary);
        }

        /* Accordion Items */
        .faq-item {
            background: var(--pure);
            border: 1px solid rgba(0, 0, 0, 0.07);
            border-radius: 12px;
            margin-bottom: 10px;
            overflow: hidden;
            transition: var(--transition-fast);
        }

        .faq-item:hover {
            border-color: rgba(255, 158, 187, 0.4);
            box-shadow: 0 4px 15px rgba(255, 158, 187, 0.1);
        }

        /* CSS-only accordion using checkbox */
        .faq-toggle {
            display: none;
        }

        .faq-question {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-md) var(--space-lg);
            cursor: pointer;
            user-select: none;
            gap: var(--space-md);
            transition: var(--transition-fast);
        }

        .faq-question:hover {
            background: #FAFAFA;
        }

        .faq-question-text {
            font-family: var(--font-subheading);
            font-size: 1.0625rem;
            color: var(--primary);
            font-weight: 600;
            line-height: 1.5;
        }

        .faq-icon {
            font-size: 1.25rem;
            color: var(--accent);
            flex-shrink: 0;
            transition: transform 0.3s ease;
            display: inline-block;
        }

        /* Answer panel */
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .faq-answer-inner {
            padding: 0 var(--space-lg) var(--space-md);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .faq-answer-inner p {
            margin-top: var(--space-sm);
            font-size: 0.9375rem;
            line-height: 1.9;
        }

        .faq-answer-inner ul {
            margin-top: var(--space-sm);
            padding-left: var(--space-md);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .faq-answer-inner ul li {
            font-size: 0.9375rem;
            color: var(--text-light);
            line-height: 1.7;
        }

        /* Open state via checkbox */
        .faq-toggle:checked ~ .faq-question .faq-icon {
            transform: rotate(45deg);
        }

        .faq-toggle:checked ~ .faq-question {
            background: var(--cream);
        }

        .faq-toggle:checked ~ .faq-question .faq-question-text {
            color: var(--accent);
        }

        .faq-toggle:checked ~ .faq-answer {
            max-height: 500px;
        }

        /* Still Need Help Banner */
        .help-banner {
            background: linear-gradient(135deg, var(--primary) 0%, #1a2634 100%);
            border-radius: 20px;
            padding: var(--space-xl);
            text-align: center;
            color: var(--pure);
            margin-top: var(--space-xl);
            position: relative;
            overflow: hidden;
        }

        .help-banner::before {
            content: '💌';
            position: absolute;
            font-size: 10rem;
            opacity: 0.05;
            right: -20px;
            bottom: -20px;
            pointer-events: none;
            user-select: none;
        }

        .help-banner h2 {
            color: var(--pure);
            margin-bottom: var(--space-sm);
            font-size: 2rem;
        }

        .help-banner p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: var(--space-lg);
            font-size: 1rem;
        }

        .help-banner-buttons {
            display: flex;
            gap: var(--space-md);
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-contact {
            padding: 14px 32px;
            border-radius: 50px;
            font-family: var(--font-button);
            font-size: 0.875rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: var(--transition-fast);
            text-decoration: none;
            display: inline-block;
        }

        .btn-contact-primary {
            background: var(--accent);
            color: var(--pure);
            border: 2px solid var(--accent);
        }

        .btn-contact-primary:hover {
            background: var(--accent-hover);
            border-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 158, 187, 0.4);
        }

        .btn-contact-secondary {
            background: transparent;
            color: var(--pure);
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .btn-contact-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--pure);
            transform: translateY(-2px);
        }

        /* Quick Links Cards */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--space-md);
            margin-bottom: var(--space-xl);
        }

        .quick-link-card {
            background: var(--pure);
            border: 1px solid rgba(0,0,0,0.07);
            border-radius: 16px;
            padding: var(--space-lg);
            text-align: center;
            text-decoration: none;
            transition: var(--transition-smooth);
            color: var(--text-dark);
        }

        .quick-link-card:hover {
            border-color: var(--accent);
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(255, 158, 187, 0.15);
            color: var(--text-dark);
        }

        .quick-link-icon {
            font-size: 1.75rem;
            display: block;
            margin-bottom: var(--space-sm);
            color: var(--accent);
            width: 60px;
            height: 60px;
            background: var(--secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--space-sm);
            border: 1px solid rgba(255, 158, 187, 0.3);
            transition: var(--transition-fast);
        }

        .quick-link-card:hover .quick-link-icon {
            background: var(--accent);
            color: white;
        }

        .quick-link-card h4 {
            color: var(--primary);
            margin-bottom: 6px;
            font-size: 1rem;
        }

        .quick-link-card p {
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .faq-layout {
                grid-template-columns: 1fr;
            }

            .faq-sidebar {
                position: static;
            }

            .faq-category-list {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .faq-category-list a {
                flex-direction: column;
                gap: 4px;
                padding: 10px 14px;
                font-size: 0.8125rem;
                text-align: center;
            }

            .faq-category-list a:hover {
                transform: none;
            }

            .quick-links {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 60px 0 40px;
            }

            .help-banner-buttons {
                flex-direction: column;
            }

            .btn-contact {
                width: 100%;
                text-align: center;
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
                <span class="badge mb-md">Support Center</span>
                <h1>How Can We <span class="highlight-word">Help</span> You?</h1>
                <p>Find answers to the most common questions about our gemstone bracelets, customization, orders, and more.</p>
            </div>
        </div>
    </header>

    <!-- Search -->
    <div class="faq-search-wrapper" role="search">
        <div class="container">
            <div class="faq-search">
                <span class="faq-search-icon" aria-hidden="true">🔍</span>
                <input
                    type="search"
                    placeholder="Search for answers... e.g. shipping, customization, returns"
                    aria-label="Search FAQ"
                    id="faqSearch"
                >
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main id="main-content">
        <div class="container">

            <!-- Quick Links -->
            <div class="quick-links" style="padding-top: var(--space-xl);" role="list" aria-label="Quick help topics">
                <a href="#ordering" class="quick-link-card" role="listitem">
                    <span class="quick-link-icon" aria-hidden="true">🛒</span>
                    <h4>Ordering</h4>
                    <p>How to place and track orders</p>
                </a>
                <a href="#customization" class="quick-link-card" role="listitem">
                    <span class="quick-link-icon" aria-hidden="true">💎</span>
                    <h4>Customization</h4>
                    <p>Design your perfect bracelet</p>
                </a>
                <a href="#shipping" class="quick-link-card" role="listitem">
                    <span class="quick-link-icon" aria-hidden="true">📦</span>
                    <h4>Shipping</h4>
                    <p>Delivery times and tracking</p>
                </a>
            </div>

            <!-- FAQ Layout -->
            <div class="faq-layout">

                <!-- Sidebar -->
                <aside class="faq-sidebar" aria-label="FAQ categories">
                    <h3>Categories</h3>
                    <ul class="faq-category-list" role="list">
                        <li>
                            <a href="#ordering" class="active" aria-current="true">
                                <span class="cat-icon" aria-hidden="true">🛒</span>
                                Ordering
                            </a>
                        </li>
                        <li>
                            <a href="#customization">
                                <span class="cat-icon" aria-hidden="true">💎</span>
                                Customization
                            </a>
                        </li>
                        <li>
                            <a href="#shipping">
                                <span class="cat-icon" aria-hidden="true">📦</span>
                                Shipping
                            </a>
                        </li>
                        <li>
                            <a href="#returns">
                                <span class="cat-icon" aria-hidden="true">↩️</span>
                                Returns
                            </a>
                        </li>
                        <li>
                            <a href="#gemstones">
                                <span class="cat-icon" aria-hidden="true">✨</span>
                                Gemstones
                            </a>
                        </li>
                        <li>
                            <a href="#account">
                                <span class="cat-icon" aria-hidden="true">👤</span>
                                Account
                            </a>
                        </li>
                    </ul>
                </aside>

                <!-- FAQ Content -->
                <div class="faq-content" role="main">

                    <!-- Ordering -->
                    <section id="ordering" aria-labelledby="ordering-title">
                        <div class="faq-section-title">
                            <span class="section-icon" aria-hidden="true">🛒</span>
                            <h2 id="ordering-title">Ordering</h2>
                        </div>

                        <div class="faq-item">
                            <input type="checkbox" id="q1" class="faq-toggle" aria-hidden="true">
                            <label class="faq-question" for="q1" aria-expanded="false">
                                <span class="faq-question-text">How do I place an order?</span>
                                <span class="faq-icon" aria-hidden="true">+</span>
                            </label>
                            <div class="faq-answer" role="region" aria-labelledby="q1">
                                <div class="faq-answer-inner">
                                    <p>Placing an order is simple! Browse our Collections page, click on any bracelet you love, choose your preferred gemstone and charm options, then click <strong>Add to cart</strong>. When you're ready, proceed to Checkout to complete your purchase.</p>
                                </div>
                            </div>
                        </div>

                        <div class="faq-item">
                            <input type="checkbox" id="q2" class="faq-toggle" aria-hidden="true">
                            <label class="faq-question" for="q2">
                                <span class="faq-question-text">Can I modify my order after placing it?</span>
                                <span class="faq-icon" aria-hidden="true">+</span>
                            </label>
                            <div class="faq-answer">
                                <div class="faq-answer-inner">
                                    <p>You can modify your order anytime before clicking the final <strong>Buy</strong> button. Once the purchase is confirmed, the order is locked and cannot be changed. If you need help, contact us as soon as possible.</p>
                                </div>
                            </div>
                        </div>

                        <div class="faq-item">
                            <input type="checkbox" id="q3" class="faq-toggle" aria-hidden="true">
                            <label class="faq-question" for="q3">
                                <span class="faq-question-text">What payment methods do you accept?</span>
                                <span class="faq-icon" aria-hidden="true">+</span>
                            </label>
                            <div class="faq-answer">
                                <div class="faq-answer-inner">
                                    <p>We currently support the following payment methods:</p>
                                    <ul>
                                        <li>Credit and debit cards (Visa, Mastercard)</li>
                                        <li>Mada</li>
                                        <li>Cash on delivery (selected areas)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="faq-item">
                            <input type="checkbox" id="q4" class="faq-toggle" aria-hidden="true">
                            <label class="faq-question" for="q4">
                                <span class="faq-question-text">How do I track my order?</span>
                                <span class="faq-icon" aria-hidden="true">+</span>
                            </label>
                            <div class="faq-answer">
                                <div class="faq-answer-inner">
                                    <p>After placing your order, you can view your order history in your account under <strong>order History</strong>. You will also receive a confirmation with order details once your purchase is complete.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Customization -->
                    <section id="customization" aria-labelledby="customization-title">
                        <div class="faq-section-title">
                            <span class="section-icon" aria-hidden="true">💎</span>
                            <h2 id="customization-title">Customization</h2>
                        </div>

                        <div class="faq-item">
                            <input type="checkbox" id="q5" class="faq-toggle" aria-hidden="true">
                            <label class="faq-question" for="q5">
                                <span class="faq-question-text">How does the bracelet customization work?</span>
                                <span class="faq-icon" aria-hidden="true">+</span>
                            </label>
                            <div class="faq-answer">
                                <div class="faq-answer-inner">
                                    <p>Visit the <strong>Customize</strong> page and use our interactive design tool to:</p>
                                    <ul>
                                        <li>Choose your gemstone type (Rose Quartz, Amethyst, Lapis Lazuli, and more)</li>
                                        <li>Select gemstone color and size</li>
                                        <li>Add decorative charms (hearts, stars, letters)</li>
                                        <li>Preview your design in real-time</li>
                                    </ul>
                                    <p>The price updates automatically as you make your selections.</p>
                                </div>
                            </div>
                        </div>

                        <div class="faq-item">
                            <input type="checkbox" id="q6" class="faq-toggle" aria-hidden="true">
                            <label class="faq-question" for="q6">
                                <span class="faq-question-text">Can I see my bracelet before buying?</span>
                                <span class="faq-icon" aria-hidden="true">+</span>
                            </label>
                            <div class="faq-answer">
                                <div class="faq-answer-inner">
                                    <p>Yes! Our customization page features a <strong>real-time visual preview</strong> that updates instantly as you choose your gemstones and charms. What you see is exactly what you'll receive.</p>
                                </div>
                            </div>
                        </div>

                        <div class="faq-item">
                            <input type="checkbox" id="q7" class="faq-toggle" aria-hidden="true">
                            <label class="faq-question" for="q7">
                                <span class="faq-question-text">How is the price calculated?</span>
                                <span class="faq-icon" aria-hidden="true">+</span>
                            </label>
                            <div class="faq-answer">
                                <div class="faq-answer-inner">
                                    <p>The final price is calculated based on your selected gemstone type, the number of stones, size, and any added charms. The price display updates live so there are no surprises at checkout.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Shipping -->
                    <section id="shipping" aria-labelledby="shipping-title">
                        <div class="faq-section-title">
                            <span class="section-icon" aria-hidden="true">📦</span>
                            <h2 id="shipping-title">Shipping</h2>
                        </div>

                        <div class="faq-item">
                            <input type="checkbox" id="q8" class="faq-toggle" aria-hidden="true">
                            <label class="faq-question" for="q8">
                                <span class="faq-question-text">How long does delivery take?</span>
                                <span class="faq-icon" aria-hidden="true">+</span>
                            </label>
                            <div class="faq-answer">
                                <div class="faq-answer-inner">
                                    <p>Standard delivery within Saudi Arabia takes <strong>3-5 business days</strong>. Express delivery (1-2 business days) is available for an additional fee. Custom orders may require extra preparation time.</p>
                                </div>
                            </div>
                        </div>

                        <div class="faq-item">
                            <input type="checkbox" id="q9" class="faq-toggle" aria-hidden="true">
                            <label class="faq-question" for="q9">
                                <span class="faq-question-text">Do you ship outside Saudi Arabia?</span>
                                <span class="faq-icon" aria-hidden="true">+</span>
                            </label>
                            <div class="faq-answer">
                                <div class="faq-answer-inner">
                                    <p>Currently, we ship within Saudi Arabia only. We are working on expanding to GCC countries. Stay tuned by subscribing to our newsletter!</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Returns -->
                    <section id="returns" aria-labelledby="returns-title">
                        <div class="faq-section-title">
                            <span class="section-icon" aria-hidden="true">↩️</span>
                            <h2 id="returns-title">Returns & Exchanges</h2>
                        </div>

                        <div class="faq-item">
                            <input type="checkbox" id="q10" class="faq-toggle" aria-hidden="true">
                            <label class="faq-question" for="q10">
                                <span class="faq-question-text">What is your return policy?</span>
                                <span class="faq-icon" aria-hidden="true">+</span>
                            </label>
                            <div class="faq-answer">
                                <div class="faq-answer-inner">
                                    <p>We accept returns within <strong>14 days</strong> of delivery for unused, undamaged items in original packaging. Custom-designed bracelets are non-refundable unless there is a manufacturing defect.</p>
                                </div>
                            </div>
                        </div>

                        <div class="faq-item">
                            <input type="checkbox" id="q11" class="faq-toggle" aria-hidden="true">
                            <label class="faq-question" for="q11">
                                <span class="faq-question-text">My bracelet arrived damaged. What do I do?</span>
                                <span class="faq-icon" aria-hidden="true">+</span>
                            </label>
                            <div class="faq-answer">
                                <div class="faq-answer-inner">
                                    <p>We're so sorry to hear that! Please contact us within 48 hours of receiving your order and include photos of the damage. We will arrange a replacement or full refund at no extra cost to you.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Gemstones -->
                    <section id="gemstones" aria-labelledby="gemstones-title">
                        <div class="faq-section-title">
                            <span class="section-icon" aria-hidden="true">✨</span>
                            <h2 id="gemstones-title">Gemstones & Care</h2>
                        </div>

                        <div class="faq-item">
                            <input type="checkbox" id="q12" class="faq-toggle" aria-hidden="true">
                            <label class="faq-question" for="q12">
                                <span class="faq-question-text">Are the gemstones natural or synthetic?</span>
                                <span class="faq-icon" aria-hidden="true">+</span>
                            </label>
                            <div class="faq-answer">
                                <div class="faq-answer-inner">
                                    <p>All our gemstones are <strong>100% natural and ethically sourced</strong>. Each stone is carefully selected for quality, color, and energy properties. We do not use synthetic or treated stones.</p>
                                </div>
                            </div>
                        </div>

                        <div class="faq-item">
                            <input type="checkbox" id="q13" class="faq-toggle" aria-hidden="true">
                            <label class="faq-question" for="q13">
                                <span class="faq-question-text">How do I care for my gemstone bracelet?</span>
                                <span class="faq-icon" aria-hidden="true">+</span>
                            </label>
                            <div class="faq-answer">
                                <div class="faq-answer-inner">
                                    <p>To keep your bracelet beautiful:</p>
                                    <ul>
                                        <li>Remove before swimming, showering, or exercising</li>
                                        <li>Avoid contact with perfume and harsh chemicals</li>
                                        <li>Clean gently with a soft dry cloth</li>
                                        <li>Store in the provided pouch away from direct sunlight</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Account -->
                    <section id="account" aria-labelledby="account-title">
                        <div class="faq-section-title">
                            <span class="section-icon" aria-hidden="true">👤</span>
                            <h2 id="account-title">Account</h2>
                        </div>

                        <div class="faq-item">
                            <input type="checkbox" id="q14" class="faq-toggle" aria-hidden="true">
                            <label class="faq-question" for="q14">
                                <span class="faq-question-text">Do I need an account to shop?</span>
                                <span class="faq-icon" aria-hidden="true">+</span>
                            </label>
                            <div class="faq-answer">
                                <div class="faq-answer-inner">
                                    <p>You can browse and add items to your cart without an account. However, creating an account lets you track orders, view your purchase history, and enjoy a faster checkout experience next time.</p>
                                </div>
                            </div>
                        </div>

                        <div class="faq-item">
                            <input type="checkbox" id="q15" class="faq-toggle" aria-hidden="true">
                            <label class="faq-question" for="q15">
                                <span class="faq-question-text">I forgot my password. How do I reset it?</span>
                                <span class="faq-icon" aria-hidden="true">+</span>
                            </label>
                            <div class="faq-answer">
                                <div class="faq-answer-inner">
                                    <p>Go to the <a href="login" style="color: var(--accent); text-decoration: underline;">Login page</a> and click <strong>Forgot Password</strong>. Enter your registered email and we'll send you a reset link within a few minutes.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Still Need Help -->
                    <div class="help-banner" role="complementary" aria-label="Contact support">
                        <h2>Still Need Help?</h2>
                        <p>Our team is here for you. Reach out and we'll get back to you as soon as possible.</p>
                        <div class="help-banner-buttons">
                            <a href="contact" class="btn-contact btn-contact-primary">📧 Contact Us</a>
                            <a href="mailto:admin@auraartifacts.com" class="btn-contact btn-contact-secondary">✉️ Email Support</a>
                        </div>
                    </div>

                </div><!-- end faq-content -->
            </div><!-- end faq-layout -->
        </div><!-- end container -->
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
                    <a href="Accessibility">Accessibility</a><br>
                    <a href="contact">Contact Us</a><br>
                    <a href="admin/admin-login">Admin Portal</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Aura Artifacts. All Rights Reserved.</p>
            </div>

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

    <script src="assets/js/faq-search.js"></script>
</body>
</html>
