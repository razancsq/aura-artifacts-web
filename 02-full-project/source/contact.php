<?php
// ============================================================
// contact.php - Contact Us Page
// Handles contact form submissions and stores messages in DB
// Uses prepared statements for database security
// ============================================================

require_once "config.php";

$cart_count = array_sum(array_column($_SESSION["cart"] ?? [], "quantity"));
$is_logged = isset($_SESSION["customer_id"]);

$success = '';
$error = '';

// --- Process Contact Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Server-side validation
    if (empty($name) || empty($email) || empty($message)) {
        $error = "Name, email, and message are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Insert contact message using prepared statement
        $stmt = $conn->prepare("INSERT INTO Contact_Message (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
        if ($stmt->execute()) {
            $success = "Thank you! Your message has been sent successfully.";
        } else {
            $error = "An error occurred while sending your message. Please try again.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contact Aura Artifacts for inquiries about our handcrafted gemstone jewelry. We are here to help with orders, custom designs, and more.">
    <title>Contact Us | Aura Artifacts</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .contact-section { padding: 80px 20px; max-width: 800px; margin: 0 auto; }
        .contact-form { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 8px 32px rgba(45,62,80,0.08); }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--primary); }
        .form-input, .form-textarea { width: 100%; padding: 12px; border: 1px solid var(--secondary); border-radius: 8px; font-family: inherit; }
        .form-textarea { resize: vertical; min-height: 150px; }
        .btn-submit { width: 100%; padding: 15px; background: var(--accent); color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: var(--primary); }
        .msg-box { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .msg-success { background: #d4edda; color: #155724; }
        .msg-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

    <!-- Navigation -->
    <?php include "includes/navbar.php"; ?>

    <div class="contact-section">
        <div class="text-center" style="margin-bottom: 40px; text-align: center;">
            <h1 class="section-title">Contact Us</h1>
            <p style="color: var(--text-light);">Have a question or feedback? We'd love to hear from you.</p>
        </div>

        <div class="contact-form">
            <?php if ($success): ?>
                <div class="msg-box msg-success"><?= $success ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="msg-box msg-error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="contact">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-input" required value="<?= htmlspecialchars($_SESSION['customer_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-textarea" required></textarea>
                </div>
                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        </div>

        <!-- Task 11: Shop Address & Location Map -->
        <div class="contact-form" style="margin-top: 30px;">
            <h2 style="font-family: 'Playfair Display', serif; color: var(--primary); margin-bottom: 20px; font-size: 1.5rem;">📍 Visit Our Store</h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div>
                    <h3 style="color: var(--primary); font-size: 1rem; margin-bottom: 8px;">Address</h3>
                    <p style="color: var(--text-light); line-height: 1.6;">
                        Aura Artifacts Jewelry<br>
                        King Faisal Road, Al Jubail<br>
                        Eastern Province, Saudi Arabia<br>
                        Postal Code: 31951
                    </p>
                </div>
                <div>
                    <h3 style="color: var(--primary); font-size: 1rem; margin-bottom: 8px;">Business Hours</h3>
                    <p style="color: var(--text-light); line-height: 1.6;">
                        Saturday - Thursday: 10 AM - 10 PM<br>
                        Friday: 2 PM - 10 PM<br>
                        <br>
                        📞 +966 50 123 4567<br>
                        ✉️ info@auraartifacts.com
                    </p>
                </div>
            </div>

            <!-- Google Maps Embed (Task 11) -->
            <div style="border-radius: 12px; overflow: hidden; border: 1px solid var(--secondary); box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d28736.95654682285!2d49.6222!3d27.0046!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e35a2b0a1b8b8b7%3A0x7c4c2e0f2b3a4d5e!2sAl%20Jubail%2C%20Saudi%20Arabia!5e0!3m2!1sen!2s!4v1700000000000!5m2!1sen!2s"
                    width="100%" 
                    height="350" 
                    style="border:0; display:block;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Aura Artifacts Store Location - Al Jubail, Saudi Arabia">
                </iframe>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <p>&copy; 2026 Aura Artifacts. All Rights Reserved.</p>
        </div>
    </footer>

<!-- Contact Form Validation Script -->
<script>
(function() {
    'use strict';
    console.log('[Contact] Validation script loaded');

    const form = document.querySelector('form[action="contact"]');
    if (!form) return;

    // --- Validation helpers ---
    function showErr(input, msg) {
        input.style.borderColor = '#e74c3c';
        input.style.boxShadow = '0 0 0 3px rgba(231,76,60,0.12)';
        let err = input.parentElement.querySelector('.contact-field-error');
        if (!err) {
            err = document.createElement('p');
            err.className = 'contact-field-error';
            err.style.cssText = 'color:#e74c3c;font-size:0.78rem;margin:4px 0 0;';
            input.parentElement.appendChild(err);
        }
        err.textContent = msg;
    }

    function clearErr(input) {
        input.style.borderColor = '';
        input.style.boxShadow = '';
        const err = input.parentElement.querySelector('.contact-field-error');
        if (err) err.remove();
    }

    // --- Validation rules ---
    const rules = {
        'name':    { test: v => v.length >= 2, msg: 'Name must be at least 2 characters.' },
        'email':   { test: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v), msg: 'Please enter a valid email address.' },
        'phone':   { test: v => v === '' || /^[\d\s\+\-\(\)]{7,20}$/.test(v), msg: 'Please enter a valid phone number.' },
        'subject': { test: v => v.length >= 3, msg: 'Subject must be at least 3 characters.' },
        'message': { test: v => v.length >= 10, msg: 'Message must be at least 10 characters.' }
    };

    // --- Real-time validation on blur ---
    form.querySelectorAll('input, textarea').forEach(input => {
        const name = input.getAttribute('name');
        if (!name || !rules[name]) return;

        input.addEventListener('blur', function() {
            try {
                const val = this.value.trim();
                if (val && !rules[name].test(val)) {
                    showErr(this, rules[name].msg);
                    console.log(`[Contact Validation] Field "${name}" failed: ${rules[name].msg}`);
                } else {
                    clearErr(this);
                }
            } catch(e) { console.error('[Contact Validation]', e); }
        });
        input.addEventListener('input', function() { clearErr(this); });
    });

    // --- Submit validation ---
    form.addEventListener('submit', function(e) {
        try {
            let hasErrors = false;
            ['name', 'email', 'subject', 'message'].forEach(name => {
                const input = form.querySelector(`[name="${name}"]`);
                if (!input) return;
                const val = input.value.trim();
                if (!val) {
                    showErr(input, 'This field is required.');
                    hasErrors = true;
                } else if (!rules[name].test(val)) {
                    showErr(input, rules[name].msg);
                    hasErrors = true;
                }
            });

            // Optional phone validation
            const phone = form.querySelector('[name="phone"]');
            if (phone && phone.value.trim() && !rules['phone'].test(phone.value.trim())) {
                showErr(phone, rules['phone'].msg);
                hasErrors = true;
            }

            if (hasErrors) {
                e.preventDefault();
                console.log('[Contact] Form blocked - validation errors');
                if (typeof window.showErrorToast === 'function') {
                    window.showErrorToast('Please fix the errors before sending.', 'warning');
                }
            } else {
                console.log('[Contact] Validation passed - submitting');
            }
        } catch(err) {
            console.error('[Contact] Validation error:', err);
        }
    });

    console.log('%c✅ Contact validation ready', 'color:#27ae60;');
})();
</script>

</body>
</html>
