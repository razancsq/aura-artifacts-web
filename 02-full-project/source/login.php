<?php
// ============================================================
// login.php - customer Login Page
// Handles user authentication with session management
// Uses prepared statements to prevent SQL injection
// ============================================================

require_once 'config.php';
$error = '';

// Redirect if already logged in
if (isset($_SESSION['customer_id'])) { header("Location: index"); exit(); }

// --- Process Login Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user input
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate required fields
    if (!$email || !$password) { $error = "Please enter email and password."; }
    else {
        // Prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM customer WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $c = $res->fetch_assoc();
            // Verify hashed password
            if (password_verify($password, $c['password'])) {
                // Set session variables for user state management
                $_SESSION['customer_id']   = $c['customer_id'];
                $_SESSION['customer_name'] = $c['full_name'];
                $_SESSION['customer_email'] = $c['email'];
                // Set "Remember Me" cookie (7 days)
                if (isset($_POST['remember'])) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('aura_email', $email, time()+604800, '/');
                    setcookie('aura_remember_token', $token, time()+604800, '/');
                    // Store the hashed token in the database
                    $hashed_token = password_hash($token, PASSWORD_DEFAULT);
                    $stmt2 = $conn->prepare("UPDATE customer SET remember_token=? WHERE customer_id=?");
                    $stmt2->bind_param("si", $hashed_token, $c['customer_id']);
                    $stmt2->execute();
                    $stmt2->close();
                } else {
                    // Clear remember cookies if unchecked
                    setcookie('aura_email', '', time()-3600, '/');
                    setcookie('aura_remember_token', '', time()-3600, '/');
                }
                header("Location: index"); exit();
            } else { $error = "Incorrect password."; }
        } else { $error = "No account found with this email."; }
        $stmt->close();
    }
}
$cart_count = 0; $is_logged = false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Sign in to your Aura Artifacts account. Access your wishlist, order history, and exclusive gemstone jewelry collections.">
  <title>Aura Artifacts | Sign In</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&family=Lato:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    .member-bg { min-height: 85vh; display: flex; justify-content: center; align-items: center; padding: 60px 20px; background: linear-gradient(135deg, var(--secondary) 0%, var(--turquoise-light) 100%); }
    .member-card { width: 100%; max-width: 440px; text-align: center; }
    .member-input { width: 100%; padding: 14px 18px; margin-bottom: 14px; border: 1.5px solid rgba(45,62,80,0.1); border-radius: 12px; background: var(--cream); font-family: var(--font-body); font-size: 0.9375rem; color: var(--text-dark); box-sizing: border-box; transition: var(--transition-fast); display: block; }
    .member-input:focus { outline: none; border-color: var(--accent); background: #fff; box-shadow: 0 0 0 3px rgba(255,158,187,0.15); }
    .member-input::placeholder { color: #b8c0cc; }
    .input-label { display: block; text-align: left; font-family: var(--font-button); font-size: 0.72rem; letter-spacing: 1.5px; text-transform: uppercase; color: var(--text-light); margin-bottom: 5px; font-weight: 600; }
    .footer-section h3 { color: var(--accent); margin-bottom: var(--space-md); font-size: 1.25rem; }
    .footer-section p, .footer-section a { color: rgba(254,254,254,0.8); font-size: 0.9375rem; line-height: 2; }
    .footer-section a:hover { color: var(--turquoise); padding-left: 8px; }
  </style>
</head>
<body>

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

<div class="member-bg">
  <div class="card member-card">
    <span class="badge mb-md">💎 Member Area</span>
    <h2 class="section-title mb-sm" style="font-size:2rem;">Sign In</h2>
    <p class="section-subtitle mb-md">Welcome back to Aura Artifacts</p>

    <?php if(!empty($error)): ?>
        <p class="error-msg" style="display:block; color:#e53935; font-size:0.82rem; margin-bottom:10px;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="login" id="loginForm">
      <label class="input-label">Email Address</label>
      <input name="email" id="emailInput" class="member-input" type="email" placeholder="your@email.com" required value="<?= htmlspecialchars($_COOKIE['aura_email'] ?? '') ?>">

      <label class="input-label">Password</label>
      <input name="password" id="passInput" class="member-input" type="password" placeholder="••••••••" required>

      <p id="loginError" style="color:#e53935; font-size:0.82rem; margin-bottom:10px; display:none;"></p>

      <!-- Remember Me checkbox - stores email in cookie for 7 days -->
      <div style="display:flex; align-items:center; gap:8px; margin-bottom:14px; justify-content:flex-start;">
        <input type="checkbox" name="remember" id="rememberMe" style="width:16px; height:16px; accent-color:var(--accent); cursor:pointer;" <?= isset($_COOKIE['aura_email']) ? 'checked' : '' ?>>
        <label for="rememberMe" style="font-size:0.85rem; color:var(--text-light); cursor:pointer;">Remember Me</label>
      </div>

      <button type="button" class="btn-gemstone" id="loginBtn" style="width:100%;" onclick="handleLogin()">Sign In</button>
    </form>

    <p class="mt-sm" style="text-align:right; font-size:0.82rem;">
      <a href="forgot-password" class="text-accent">Forgot password?</a>
    </p>

    <p class="mt-md" style="font-size:0.875rem; color:var(--text-light);">
      Don't have an account?
      <a href="register" class="text-accent" style="font-weight:600;">Create one</a>
    </p>
  </div>
</div>

<footer class="footer">
  <div class="container">
    <div class="footer-content">
      <div class="footer-section"><h3>AURA ARTIFACTS</h3><p>Handcrafted gemstone bracelets that elevate your style with natural beauty.</p></div>
      <div class="footer-section"><h3>Quick Links</h3><p><a href="index">Home</a></p><p><a href="collections">Collections</a></p><p><a href="about">About Us</a></p></div>
      <div class="footer-section"><h3>customer Care</h3><p><a href="SizeGuide">Size Guide</a></p><p><a href="help">FAQ</a></p></div>
      <div class="footer-section"><h3>Connect With Us</h3><p>Email: <a href="mailto:hello@auraartifacts.com">hello@auraartifacts.com</a></p><p>Instagram: @auraartifacts</p></div>
    </div>
    <div class="footer-bottom"><p>&copy; 2026 Aura Artifacts. All Rights Reserved.</p></div>
  </div>
</footer>

<script>
  function handleLogin() {
    const email = document.getElementById('emailInput').value.trim();
    const pass  = document.getElementById('passInput').value.trim();
    const error = document.getElementById('loginError');
    const btn   = document.getElementById('loginBtn');

    if (!email || !pass) {
      error.textContent = '⚠️ Please fill in all fields.';
      error.style.display = 'block'; return;
    }

    error.style.display = 'none';
    btn.textContent = 'Signing in...';
    btn.style.opacity = '0.75';
    btn.style.pointerEvents = 'none';

    document.getElementById('loginForm').submit();
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') handleLogin();
  });

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
