<?php 
require_once "config.php"; 
$cart_count = array_sum(array_column($_SESSION["cart"] ?? [], "quantity")); 
$is_logged = isset($_SESSION["customer_id"]);

// Detect where user came from (admin or customer login)
$from_admin = isset($_GET['from']) && $_GET['from'] === 'admin';
$back_url = $from_admin ? BASE_URL . '/admin/admin-login' : BASE_URL . '/login';
$back_label = $from_admin ? 'Back to Admin Login' : 'Back to Login';

$fp_success = '';
$fp_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($conn->real_escape_string($_POST['email'] ?? ''));
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fp_error = "Please enter a valid email address.";
    } else {
        // Check if email exists in customer or Admin tables (prepared statements)
        $user_type = null;
        $stmt = $conn->prepare("SELECT email FROM customer WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $check = $stmt->get_result();
        if ($check && $check->num_rows > 0) { $user_type = 'customer'; }
        $stmt->close();
        
        if (!$user_type) {
            $stmt = $conn->prepare("SELECT email FROM admin WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $check = $stmt->get_result();
            if ($check && $check->num_rows > 0) { $user_type = 'admin'; }
            $stmt->close();
        }
        
        if ($user_type) {
            $token = bin2hex(random_bytes(32));
            $stmt = $conn->prepare("INSERT INTO password_reset_request (email, user_type, token, status) VALUES (?, ?, ?, 'pending')");
            $stmt->bind_param("sss", $email, $user_type, $token);
            $stmt->execute();
            $stmt->close();
            $fp_success = "Password reset request submitted successfully! We will contact you shortly.";
        } else {
            $fp_error = "This email is not registered. Please check your email and try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Aura Artifacts</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Playfair+Display:wght@400;600&family=Poppins:wght@300;400;500;600&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
        <style>
.skip-link {
            position: absolute; top: -40px; left: 0;
            background: var(--primary); color: #fff;
            padding: 8px 16px; text-decoration: none;
            font-size: 0.875rem; z-index: 100;
            transition: top 0.2s;
        }
        .skip-link:focus { top: 0; }

        .admin-login-wrapper {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .login-panel-left {
            background: linear-gradient(145deg, var(--primary) 0%, #1a2a3a 60%, #0f1e2e 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 50px;
            position: relative;
            overflow: hidden;
        }

        .login-panel-left::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255,158,187,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .login-panel-left::after {
            content: '';
            position: absolute;
            bottom: -80px; left: -80px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(141,212,204,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .login-brand { text-align: center; position: relative; z-index: 2; }

        .login-brand .gem-icon {
            font-size: 3.5rem;
            margin-bottom: 20px;
            display: block;
            animation: floatGem 3s ease-in-out infinite;
        }

        @keyframes floatGem {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-12px); }
        }

        .login-brand h1 {
            font-family: var(--font-heading);
            color: #FFFFFF;
            font-size: 2.5rem;
            letter-spacing: 3px;
            margin-bottom: 8px;
        }

        .login-brand .brand-sub {
            font-family: var(--font-button);
            color: var(--accent);
            font-size: 0.8rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-bottom: 50px;
        }

        .login-features { list-style: none; width: 100%; max-width: 280px; }

        .login-features li {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.75);
            font-family: var(--font-body);
            font-size: 0.9rem;
            font-weight: 300;
            letter-spacing: 0.3px;
        }

        .login-features li:last-child { border-bottom: none; }

        .login-features li .feat-icon {
            width: 32px; height: 32px;
            background: rgba(255,158,187,0.15);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0;
        }

        .login-panel-right {
            background: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 50px;
        }

        .login-form-box { width: 100%; max-width: 420px; }

        .login-form-header { margin-bottom: 40px; }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--secondary);
            color: var(--accent);
            padding: 6px 16px;
            border-radius: 20px;
            font-family: var(--font-button);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .login-form-header h2 {
            font-family: var(--font-heading);
            font-size: 2.25rem;
            color: var(--primary);
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .login-form-header p {
            font-size: 0.9375rem;
            color: var(--text-light);
            margin: 0;
            line-height: 1.6;
        }

        .admin-form { display: flex; flex-direction: column; gap: 20px; }

        .form-group { display: flex; flex-direction: column; gap: 8px; }

        .form-group label {
            font-family: var(--font-button);
            font-size: 0.8125rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--primary);
        }

        .input-wrapper { position: relative; }

        .input-icon {
            position: absolute;
            left: 16px; top: 50%;
            transform: translateY(-50%);
            font-size: 1.1rem;
            pointer-events: none;
            opacity: 0.5;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 46px;
            border: 1.5px solid #E8E8E8;
            border-radius: 12px;
            font-family: var(--font-body);
            font-size: 0.9375rem;
            color: var(--text-dark);
            background: #FAFAFA;
            transition: var(--transition-fast);
            outline: none;
        }

        .form-input:focus {
            border-color: var(--accent);
            background: #FFFFFF;
            box-shadow: 0 0 0 4px rgba(255,158,187,0.12);
        }

        .form-input::placeholder { color: #BABABA; font-size: 0.875rem; }

        .login-submit-btn {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: #FFFFFF;
            border: none;
            border-radius: 12px;
            font-family: var(--font-button);
            font-size: 0.9375rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: var(--transition-smooth);
            position: relative;
            overflow: hidden;
            margin-top: 8px;
        }

        .login-submit-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--accent), var(--turquoise));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .login-submit-btn:hover::before { opacity: 1; }
        .login-submit-btn span { position: relative; z-index: 1; }

        .login-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(45,62,80,0.3);
        }

        .login-divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 8px 0;
        }

        .login-divider::before,
        .login-divider::after {
            content: ''; flex: 1;
            height: 1px; background: #EBEBEB;
        }

        .login-divider span {
            font-size: 0.8125rem;
            color: #BABABA;
            font-family: var(--font-body);
            white-space: nowrap;
        }

        .security-notice {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 14px 16px;
            background: #FFF8F0;
            border: 1px solid rgba(255,158,187,0.2);
            border-radius: 10px;
            margin-top: 4px;
        }

        .security-notice .notice-icon { font-size: 1rem; flex-shrink: 0; margin-top: 2px; }

        .security-notice p {
            font-size: 0.8125rem;
            color: var(--text-light);
            margin: 0;
            line-height: 1.5;
        }

        .back-to-site { text-align: center; margin-top: 24px; }

        .back-to-site a {
            font-size: 0.875rem;
            color: var(--text-light);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition-fast);
            text-decoration: none;
        }

        .back-to-site a:hover { color: var(--primary); }

        @media (max-width: 768px) {
            .admin-login-wrapper { grid-template-columns: 1fr; }
            .login-panel-left { display: none; }
            .login-panel-right { padding: 40px 24px; }
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <div class="admin-login-wrapper">

        <!-- Left Decorative Panel -->
        <div class="login-panel-left" role="complementary" aria-label="Aura Artifacts branding">
            <div class="login-brand">
                <span class="gem-icon" aria-hidden="true">💎</span>
                <h1>AURA ARTIFACTS</h1>
                <p class="brand-sub">Account Recovery</p>

                <ul class="login-features" aria-label="Why reset your password">
                    <li><span class="feat-icon" aria-hidden="true">🔒</span>Secure password reset</li>
                    <li><span class="feat-icon" aria-hidden="true">✉️</span>Email verification</li>
                    <li><span class="feat-icon" aria-hidden="true">🛡️</span>Your data stays protected</li>
                    <li><span class="feat-icon" aria-hidden="true">⚡</span>Quick & easy process</li>
                    <li><span class="feat-icon" aria-hidden="true">💎</span>Get back to shopping</li>
                </ul>
            </div>
        </div>

        <!-- Right Form Panel -->
        <main class="login-panel-right" id="main-content">
            <div class="login-form-box">

                <header class="login-form-header">
                    <div class="admin-badge">
                        <span aria-hidden="true">🔑</span>
                        Account Recovery
                    </div>
                    <h2>Forgot Password?</h2>
                    <p>Enter your email address and we'll process your password reset request.</p>
                </header>

                <?php if ($fp_success): ?>
                    <div style="background: #d4edda; color: #155724; padding: 14px 16px; border-radius: 10px; margin-bottom: 10px; font-size: 0.9rem; border: 1px solid #c3e6cb;">
                        ✅ <?= htmlspecialchars($fp_success) ?>
                    </div>
                <?php endif; ?>
                <?php if ($fp_error): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 14px 16px; border-radius: 10px; margin-bottom: 10px; font-size: 0.9rem; border: 1px solid #f5c6cb;">
                        <?= htmlspecialchars($fp_error) ?>
                    </div>
                <?php endif; ?>

                <form class="admin-form" action="<?= $from_admin ? '?from=admin' : '' ?>" method="POST" novalidate>

                    <div class="form-group">
                        <label for="reset-email">Email Address</label>
                        <div class="input-wrapper">
                            <span class="input-icon" aria-hidden="true">✉️</span>
                            <input
                                class="form-input"
                                type="email"
                                id="reset-email"
                                name="email"
                                placeholder="admin@auraartifacts.com"
                                required
                                autocomplete="email"
                                aria-required="true"
                            >
                        </div>
                    </div>

                    <button type="submit" class="login-submit-btn">
                        <span>Send Reset Link</span>
                    </button>

                    <div class="login-divider" aria-hidden="true">
                        <span>secure connection</span>
                    </div>

                    <div class="security-notice" role="note">
                        <span class="notice-icon" aria-hidden="true">💡</span>
                        <p>Check your spam folder if you don't receive the email within a few minutes. Make sure you entered the correct admin email address.</p>
                    </div>

                </form>

                <div class="back-to-site">
                    <a href="<?= htmlspecialchars($back_url) ?>">
                        <span aria-hidden="true">←</span>
                        <?= $back_label ?>
                    </a>
                    &nbsp;·&nbsp;
                    <a href="index" aria-label="View Aura Artifacts website">
                        <span aria-hidden="true">🌐</span>
                        View Website
                    </a>
                </div>

            </div>
        </main>

    </div>

</body>
</html>
