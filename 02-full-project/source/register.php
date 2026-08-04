<?php
// ============================================================
// register.php - customer Registration Page
// Handles new customer account creation with validation
// Uses prepared statements for database security
// ============================================================

require_once 'config.php';
$error = ''; $success = '';

// --- Process Registration Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user inputs
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email']     ?? '');
    $phone     = trim($_POST['phone']     ?? '');
    $address   = trim($_POST['address']   ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm']  ?? '';

    // Server-side validation
    if (!$full_name || !$email || !$password) { $error = "Please fill all required fields."; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = "Please enter a valid email address."; }
    elseif ($password !== $confirm) { $error = "Passwords do not match."; }
    elseif (strlen($password) < 6)  { $error = "Password must be at least 6 characters."; }
    else {
        // Check if email already exists (prepared statement)
        $stmt = $conn->prepare("SELECT customer_id FROM customer WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $check = $stmt->get_result();

        if ($check->num_rows > 0) { $error = "Email already registered."; }
        else {
            // Hash password securely using bcrypt
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Insert new customer (prepared statement)
            $stmt2 = $conn->prepare("INSERT INTO customer (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
            $stmt2->bind_param("sssss", $full_name, $email, $hashed, $phone, $address);
            $stmt2->execute();
            $cid = $conn->insert_id;
            $stmt2->close();

            // Create a shopping cart for the new customer
            $stmt3 = $conn->prepare("INSERT INTO cart (customer_id) VALUES (?)");
            $stmt3->bind_param("i", $cid);
            $stmt3->execute();
            $stmt3->close();

            // Auto-login after registration
            $_SESSION['customer_id']   = $cid;
            $_SESSION['customer_name'] = $full_name;
            $_SESSION['customer_email'] = $email;
            header("Location: index"); exit();
        }
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
  <meta name="description" content="Create your Aura Artifacts account. Join our community and enjoy exclusive access to handcrafted gemstone jewelry.">
  <title>Aura Artifacts | Create Account</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&family=Lato:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">

  <style>
    .member-bg {
      min-height: 85vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 60px 20px;
      background: linear-gradient(135deg, var(--secondary) 0%, var(--turquoise-light) 100%);
    }

    .member-card { width: 100%; max-width: 480px; text-align: center; }

    .member-input {
      width: 100%;
      padding: 14px 18px;
      margin-bottom: 14px;
      border: 1.5px solid rgba(45,62,80,0.1);
      border-radius: 12px;
      background: var(--cream);
      font-family: var(--font-body);
      font-size: 0.9375rem;
      color: var(--text-dark);
      box-sizing: border-box;
      transition: var(--transition-fast);
      display: block;
    }
    .member-input:focus {
      outline: none;
      border-color: var(--accent);
      background: #fff;
      box-shadow: 0 0 0 3px rgba(255,158,187,0.15);
    }
    .member-input::placeholder { color: #b8c0cc; }

    .input-label {
      display: block;
      text-align: left;
      font-family: var(--font-button);
      font-size: 0.72rem;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: var(--text-light);
      margin-bottom: 5px;
      font-weight: 600;
    }

    .error-msg {
      color: #e53935;
      font-size: 0.82rem;
      margin-bottom: 10px;
      display: none;
      text-align: left;
    }

    .success-msg {
      color: #2e7d32;
      font-size: 0.85rem;
      margin-bottom: 10px;
      display: none;
      text-align: center;
      background: rgba(76,175,80,0.08);
      padding: 10px;
      border-radius: 8px;
    }
  </style>
</head>
<body>

<!-- Navbar -->
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
        left: 50%;
        transform: translateX(-50%);
        background: white;
        border: none;
        border-top: 1.5px solid #D4AF37;
        border-radius: 0;
        min-width: 250px;
        max-height: 300px;
        overflow-y: auto;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        z-index: 1000;
        margin-top: 8px;
        right: auto;
    }

    .search-item {
        padding: 12px 15px;
        border-bottom: 1px solid #F0F0F0;
        cursor: pointer;
        transition: var(--transition-fast);
        text-align: center;
    }

    .search-item:hover {
        background: #FFFDFB;
        padding-left: 15px;
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

    <span class="badge mb-md">✨ Join Aura</span>
    <h2 class="section-title mb-sm" style="font-size:2rem;">Create Account</h2>
    <p class="section-subtitle mb-md">Start your gemstone journey</p>

    <div id="successMsg" class="success-msg">
      ✓ Account created! <a href="profile" class="text-accent" style="font-weight:600;">Sign in now</a>
    </div>

    <?php if(!empty($error)): ?>
        <p class="error-msg" style="display:block;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="register" id="registerForm">
      <label class="input-label">Full Name</label>
      <input name="full_name" id="regName" class="member-input" type="text" placeholder="Your full name" required>

      <label class="input-label">Email Address</label>
      <input name="email" id="regEmail" class="member-input" type="email" placeholder="your@email.com" required>

      <label class="input-label">Password</label>
      <input name="password" id="regPass" class="member-input" type="password" placeholder="At least 6 characters" required>

      <label class="input-label">Confirm Password</label>
      <input name="confirm" id="regConfirm" class="member-input" type="password" placeholder="Repeat your password" required>

      <p id="regError" class="error-msg"></p>

      <button type="button" class="btn-gemstone" id="registerBtn" style="width:100%;" onclick="handleRegister()">
        Create Account
      </button>
    </form>

    <p class="mt-md" style="font-size:0.875rem; color:var(--text-light);">
      Already have an account?
      <a href="profile" class="text-accent" style="font-weight:600;">Sign In</a>
    </p>

  </div>
</div>

<!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="footer-bottom">
      <p>&copy; 2026 Aura Artifacts. All Rights Reserved. Crafted with love and gemstone magic ✨</p>
    </div>
  </div>
</footer>

<script>
  function handleRegister() {
    const name    = document.getElementById('regName').value.trim();
    const email   = document.getElementById('regEmail').value.trim();
    const pass    = document.getElementById('regPass').value.trim();
    const confirm = document.getElementById('regConfirm').value.trim();
    const error   = document.getElementById('regError');
    const btn     = document.getElementById('registerBtn');

    error.style.display = 'none';

    if (!name || !email || !pass || !confirm) {
      error.textContent = '⚠️ Please fill in all fields.';
      error.style.display = 'block';
      return;
    }

    if (pass.length < 6) {
      error.textContent = '⚠️ Password must be at least 6 characters.';
      error.style.display = 'block';
      return;
    }

    if (pass !== confirm) {
      error.textContent = '✗ Passwords do not match.';
      error.style.display = 'block';
      return;
    }

    btn.textContent = 'Creating account...';
    btn.style.opacity = '0.75';
    btn.style.pointerEvents = 'none';

    document.getElementById('registerForm').submit();
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') handleRegister();
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
