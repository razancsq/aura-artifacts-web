<?php
require_once 'config.php';

$is_logged = isset($_SESSION['customer_id']);
$error = '';

// Calculate cart summary
$cart_items = [];
$subtotal = 0;

if ($is_logged) {
    $cid = (int)$_SESSION['customer_id'];
    // Prepared statement: get cart ID
    $stmt = $conn->prepare("SELECT cart_id FROM cart WHERE customer_id = ?");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $cart_id = $res->fetch_assoc()['cart_id'];
        $stmt->close();
        // Prepared statement: get cart items with product details
        $stmt2 = $conn->prepare("SELECT ci.*, p.name, p.base_price, p.image_url, bp.custom_images, bp.total_price AS custom_price 
                FROM cart_item ci 
                LEFT JOIN product p ON ci.product_id = p.product_id
                LEFT JOIN braceletpreview bp ON ci.preview_id = bp.preview_id
                WHERE ci.cart_id = ?");
        $stmt2->bind_param("i", $cart_id);
        $stmt2->execute();
        $items_res = $stmt2->get_result();
        while ($row = $items_res->fetch_assoc()) {
            if ($row['preview_id']) {
                $subtotal += $row['custom_price'] * $row['quantity'];
                $cart_items[] = [
                    'name' => 'Custom Bracelet',
                    'price' => $row['custom_price'],
                    'quantity' => $row['quantity'],
                    'image' => 'assets/images/custom_bracelet.png',
                    'is_custom' => true,
                    'preview_id' => $row['preview_id']
                ];
            } else {
                $subtotal += $row['base_price'] * $row['quantity'];
                $cart_items[] = [
                    'name' => $row['name'],
                    'price' => $row['base_price'],
                    'quantity' => $row['quantity'],
                    'image' => $row['image_url'],
                    'is_custom' => false,
                    'product_id' => $row['product_id']
                ];
            }
        }
    }
} else {
    // Session cart
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            if (isset($item['is_custom']) && $item['is_custom']) {
                $price = $item['price'];
                $name = 'Custom Bracelet';
                $image = 'assets/images/custom_bracelet.png';
            } else {
                $pid = intval($item['product_id']);
                // Prepared statement: get product info for guest cart
                $stmt = $conn->prepare("SELECT name, base_price, image_url FROM product WHERE product_id = ?");
                $stmt->bind_param("i", $pid);
                $stmt->execute();
                $prod_res = $stmt->get_result();
                if ($prod_res && $prod = $prod_res->fetch_assoc()) {
                    $price = $prod['base_price'];
                    $name = $prod['name'];
                    $image = $prod['image_url'];
                } else {
                    continue;
                }
            }
            $subtotal += $price * $item['quantity'];
            $cart_items[] = [
                'name' => $name,
                'price' => $price,
                'quantity' => $item['quantity'],
                'image' => $image
            ];
        }
    }
}

$shipping = 10.00;
$total = $subtotal > 0 ? $subtotal + $shipping : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'buy') {
    if (!$is_logged) { header("Location: login"); exit(); }
    
    // Sanitize input (prepared statements handle SQL safety)
    $address = trim($_POST['address'] ?? '');
    $payment = trim($_POST['payment'] ?? 'credit_card');
    
    // Re-verify cart from DB using prepared statement
    $stmt = $conn->prepare("SELECT cart_id FROM cart WHERE customer_id = ?");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $cart_res = $stmt->get_result();
    if ($cart_res->num_rows === 0 || $total == 0) { 
        $error = "Your cart is empty.";
        $stmt->close();
    } else {
        $cart_id = $cart_res->fetch_assoc()['cart_id'];
        $stmt->close();
        // Prepared statement: get cart items for order processing
        $stmt = $conn->prepare("SELECT ci.*, p.base_price, p.stock, p.name, bp.total_price AS custom_price 
                                   FROM cart_item ci 
                                   LEFT JOIN product p ON ci.product_id = p.product_id
                                   LEFT JOIN braceletpreview bp ON ci.preview_id = bp.preview_id
                                   WHERE ci.cart_id = ?");
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        $items_res = $stmt->get_result();
                                   
        $db_cart = [];
        $db_total = 0;
        $ok = true;
        while ($row = $items_res->fetch_assoc()) {
            if ($row['preview_id']) {
                $db_total += $row['custom_price'] * $row['quantity'];
                $db_cart[] = $row;
            } else {
                if ($row['stock'] < $row['quantity']) {
                    $error = "Sorry, '{$row['name']}' only has {$row['stock']} in stock.";
                    $ok = false;
                    break;
                }
                $db_total += $row['base_price'] * $row['quantity'];
                $db_cart[] = $row;
            }
        }
        
        $db_total += $shipping;
        
        if ($ok && count($db_cart) > 0) {
            // Prepared statement: create order
            $stmt = $conn->prepare("INSERT INTO `order` (customer_id, total_price, address, payment) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idss", $cid, $db_total, $address, $payment);
            $stmt->execute();
            $oid = $conn->insert_id;
            $stmt->close();
            foreach ($db_cart as $row) {
                $qty = $row['quantity'];
                if ($row['preview_id']) {
                    $up = $row['custom_price'];
                    $prev_id = $row['preview_id'];
                    // Prepared statement: insert custom order item
                    $stmt = $conn->prepare("INSERT INTO order_item (order_id, preview_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiid", $oid, $prev_id, $qty, $up);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $up = $row['base_price'];
                    $pid = $row['product_id'];
                    // Prepared statement: insert regular order item
                    $stmt = $conn->prepare("INSERT INTO order_item (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiid", $oid, $pid, $qty, $up);
                    $stmt->execute();
                    $stmt->close();
                    // Prepared statement: update stock
                    $stmt = $conn->prepare("UPDATE product SET stock = stock - ? WHERE product_id = ?");
                    $stmt->bind_param("ii", $qty, $pid);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            // Prepared statement: clear cart items
            $stmt = $conn->prepare("DELETE FROM cart_item WHERE cart_id = ?");
            $stmt->bind_param("i", $cart_id);
            $stmt->execute();
            $stmt->close();
            setcookie('last_order_id', $oid, time()+2592000, '/');
            header("Location: order-confirmation.php?order_id=$oid"); exit();
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
    <meta name="description" content="Complete your purchase at Aura Artifacts. Secure checkout for handcrafted gemstone jewelry with worldwide shipping.">
    <title>Aura Artifacts | Checkout</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .cart-item-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .cart-item-info { display: flex; align-items: center; gap: 15px; margin-bottom: 10px; }
    </style>
</head>
<body>

    <?php include "includes/navbar.php"; ?>

<main class="cart-container">
    <h1 class="text-center" style="margin-top: var(--space-xl);">Checkout</h1>
    
    <?php if($error): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="checkout" method="POST" class="checkout-wrapper">
        <input type="hidden" name="action" value="buy">
        <!-- Left Column: Forms -->
        <div class="checkout-form">
            <?php if(!$is_logged): ?>
                <div style="background: var(--cream); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <p style="margin:0;">You must be <a href="login" style="color:var(--accent); font-weight:bold;">logged in</a> to complete your purchase.</p>
                </div>
                
                <!-- Toast Notification for Guest Users -->
                <div id="guestToast" style="position: fixed; bottom: 30px; right: 30px; background: #ff4757; color: white; padding: 16px 24px; border-radius: 8px; box-shadow: 0 10px 25px rgba(255,71,87,0.4); z-index: 9999; transform: translateY(100px); opacity: 0; transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55); display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 1.2rem;">⚠️</span>
                    <div>
                        <strong style="display: block; margin-bottom: 4px;">Login Required</strong>
                        <span style="font-size: 0.9rem;">You must be logged in to complete your purchase.</span>
                    </div>
                    <button onclick="document.getElementById('guestToast').style.display='none'" style="background: none; border: none; color: white; margin-left: 10px; cursor: pointer; font-size: 1.2rem;">✕</button>
                </div>
                <script>
                    setTimeout(() => {
                        const toast = document.getElementById('guestToast');
                        if(toast) {
                            toast.style.transform = 'translateY(0)';
                            toast.style.opacity = '1';
                        }
                    }, 500);
                </script>
            <?php endif; ?>

            <!-- Contact Info -->
            <div class="form-section">
                <h3>Contact Information</h3>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="checkout-input" required placeholder="you@example.com" value="<?= $is_logged ? htmlspecialchars($_SESSION['customer_email'] ?? '') : '' ?>">
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="form-section">
                <h3>Shipping Address</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="fname">First Name</label>
                        <input type="text" id="fname" name="fname" class="checkout-input" required value="<?= $is_logged ? htmlspecialchars($_SESSION['customer_name'] ?? '') : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="lname">Last Name</label>
                        <input type="text" id="lname" name="lname" class="checkout-input" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" class="checkout-input" required placeholder="123 Jewelry Lane">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" class="checkout-input" required pattern="[A-Za-z\s\-]+" title="City name must contain letters only">
                    </div>
                    <div class="form-group">
                        <label for="zip">Zip Code</label>
                        <input type="text" id="zip" name="zip" class="checkout-input" required pattern="[0-9]+" title="Zip code must contain numbers only">
                    </div>
                </div>
            </div>

            <!-- Payment -->
            <div class="form-section">
                <h3>Payment</h3>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment" id="paymentMethod" class="checkout-input" required>
                        <option value="credit_card">Credit Card</option>
                        <option value="cash_on_delivery">Cash on Delivery</option>
                    </select>
                </div>
                <div id="creditCardFields">
                    <div class="form-group">
                        <label for="card-name">Name on Card</label>
                        <input type="text" id="card-name" class="checkout-input cc-field" required>
                    </div>
                    <div class="form-group">
                        <label for="card-number">Card Number</label>
                        <input type="text" id="card-number" class="checkout-input cc-field" placeholder="0000 0000 0000 0000" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry">Expiry</label>
                            <input type="text" id="expiry" placeholder="MM/YY" class="checkout-input cc-field" required>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" placeholder="123" class="checkout-input cc-field" required>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="cart-btn cart-btn-primary cart-btn-full" <?= !$is_logged || $total == 0 ? 'disabled style="opacity:0.5;"' : '' ?>>Place order</button>
        </div>

        <!-- Right Column: Summary -->
        <div class="cart-summary">
            <h3>order Summary</h3>
            <div style="margin-bottom: var(--space-md);">
                <?php if(empty($cart_items)): ?>
                    <p>Your cart is empty.</p>
                <?php else: ?>
                    <?php foreach($cart_items as $item): ?>
                    <div class="cart-item-info">
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-img" onerror="this.src='assets/images/ABOUT US.jpg'">
                        <div>
                            <h4 style="font-size: 1rem;"><?= htmlspecialchars($item['name']) ?></h4>
                            <p style="font-size: 0.8rem;">Qty: <?= $item['quantity'] ?> - $<?= number_format($item['price'], 2) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="form-group" style="border-top: 1px solid var(--secondary); padding-top: var(--space-sm);"></div>

            <div class="summary-row">
                <span>Subtotal</span>
                <span>$<?= number_format($subtotal, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping</span>
                <span>$<?= $subtotal > 0 ? number_format($shipping, 2) : '0.00' ?></span>
            </div>
            <div class="summary-row summary-total">
                <span>Total</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>
        </div>
    </form>
</main>

<footer class="footer">
    <div class="container text-center">
        <p>&copy; 2026 Aura Artifacts. All Rights Reserved.</p>
    </div>
</footer>

<!-- Checkout Form Validation Script -->
<script>
(function() {
    'use strict';
    console.log('[Checkout] Validation script loaded');

    // --- Payment method toggle ---
    const paymentSelect = document.getElementById('paymentMethod');
    const ccFieldsWrapper = document.getElementById('creditCardFields');
    const ccInputs = document.querySelectorAll('.cc-field');

    function toggleCreditCardFields() {
        if (!paymentSelect || !ccFieldsWrapper) return;
        const isCreditCard = paymentSelect.value === 'credit_card';
        ccFieldsWrapper.style.display = isCreditCard ? '' : 'none';
        ccInputs.forEach(input => {
            if (isCreditCard) {
                input.setAttribute('required', 'required');
            } else {
                input.removeAttribute('required');
                input.value = '';
                // Clear any errors on hidden fields
                input.style.borderColor = '';
                input.style.boxShadow = '';
                const err = input.parentElement.querySelector('.checkout-field-error');
                if (err) err.remove();
            }
        });
    }

    if (paymentSelect) {
        paymentSelect.addEventListener('change', toggleCreditCardFields);
        toggleCreditCardFields(); // Initialize on page load
    }

    const form = document.querySelector('.checkout-wrapper');
    if (!form) return;

    // --- Validation Rules ---
    const rules = {
        'email':       { test: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v), msg: 'Please enter a valid email address.' },
        'fname':       { test: v => /^[A-Za-z\s\-]{2,}$/.test(v), msg: 'First name must contain letters only - at least 2 characters.' },
        'lname':       { test: v => /^[A-Za-z\s\-]{2,}$/.test(v), msg: 'Last name must contain letters only - at least 2 characters.' },
        'address':     { test: v => v.length >= 5, msg: 'Please enter a valid address (at least 5 characters).' },
        'city':        { test: v => /^[A-Za-z\s\-]{2,}$/.test(v), msg: 'City name must contain letters only - no numbers allowed.' },
        'zip':         { test: v => /^\d{3,10}$/.test(v), msg: 'Zip code must contain numbers only.' },
        'card-name':   { test: v => v.length >= 2, msg: 'Please enter the name on your card.' },
        'card-number': { test: v => /^[\d\s]{13,19}$/.test(v.replace(/\s/g,'')), msg: 'Card number must be 13-19 digits.' },
        'expiry':      { test: v => /^(0[1-9]|1[0-2])\/\d{2}$/.test(v), msg: 'Please use MM/YY format.' },
        'cvv':         { test: v => /^\d{3,4}$/.test(v), msg: 'CVV must be 3 or 4 digits.' }
    };

    // --- Toast notification helper ---
    function showToast(msg) {
        if (typeof window.showErrorToast === 'function') {
            window.showErrorToast(msg, 'warning');
        } else {
            let toast = document.getElementById('checkoutToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'checkoutToast';
                toast.style.cssText = 'position:fixed;top:30px;right:30px;background:#ff9800;color:#fff;padding:16px 24px;border-radius:12px;box-shadow:0 10px 25px rgba(255,152,0,0.4);z-index:9999;display:flex;align-items:center;gap:12px;font-family:var(--font-body);font-size:0.9rem;transition:opacity 0.4s ease;';
                document.body.appendChild(toast);
            }
            toast.innerHTML = '<span style="font-size:1.2rem;">⚠️</span><div><strong style="display:block;margin-bottom:2px;">Invalid Input</strong>' + msg + '</div>';
            toast.style.display = 'flex';
            toast.style.opacity = '1';
            clearTimeout(toast._timer);
            toast._timer = setTimeout(function(){ toast.style.opacity = '0'; setTimeout(function(){ toast.style.display = 'none'; }, 400); }, 3000);
        }
    }

    // --- Style helper ---
    function showFieldError(input, msg) {
        input.style.borderColor = '#e74c3c';
        input.style.boxShadow = '0 0 0 3px rgba(231,76,60,0.12)';
        let err = input.parentElement.querySelector('.checkout-field-error');
        if (!err) {
            err = document.createElement('p');
            err.className = 'checkout-field-error';
            err.style.cssText = 'color:#e74c3c;font-size:0.78rem;margin:4px 0 0;';
            input.parentElement.appendChild(err);
        }
        err.textContent = msg;
    }

    function clearFieldError(input) {
        input.style.borderColor = '';
        input.style.boxShadow = '';
        const err = input.parentElement.querySelector('.checkout-field-error');
        if (err) err.remove();
    }

    // --- Real-time validation on blur with toast ---
    Object.keys(rules).forEach(id => {
        const input = document.getElementById(id);
        if (!input) return;
        input.addEventListener('blur', function() {
            try {
                const val = this.value.trim();
                if (val && !rules[id].test(val)) {
                    showFieldError(this, rules[id].msg);
                    showToast(rules[id].msg);
                    console.log(`[Checkout Validation] Field #${id} failed: ${rules[id].msg}`);
                } else {
                    clearFieldError(this);
                }
            } catch(e) {
                console.error('[Checkout Validation] Error:', e);
            }
        });
        input.addEventListener('input', function() { clearFieldError(this); });
    });

    // --- City field: reject numbers in real-time with toast ---
    const cityInput = document.getElementById('city');
    if (cityInput) {
        cityInput.addEventListener('input', function() {
            if (/\d/.test(this.value)) {
                this.value = this.value.replace(/[0-9]/g, '');
                showFieldError(this, 'City name must contain letters only - no numbers allowed.');
                showToast('City name must contain letters only - numbers are not allowed.');
            }
        });
    }

    // --- Zip field: reject letters in real-time with toast ---
    const zipInput = document.getElementById('zip');
    if (zipInput) {
        zipInput.addEventListener('input', function() {
            if (/[^0-9]/.test(this.value)) {
                this.value = this.value.replace(/[^0-9]/g, '');
                showFieldError(this, 'Zip code must contain numbers only.');
                showToast('Zip code must contain numbers only - letters are not allowed.');
            }
        });
    }

    // --- First/Last name: reject numbers in real-time ---
    ['fname', 'lname'].forEach(id => {
        const input = document.getElementById(id);
        if (!input) return;
        input.addEventListener('input', function() {
            if (/\d/.test(this.value)) {
                this.value = this.value.replace(/[0-9]/g, '');
                const label = id === 'fname' ? 'First name' : 'Last name';
                showFieldError(this, label + ' must contain letters only.');
                showToast(label + ' must contain letters only - numbers are not allowed.');
            }
        });
    });

    // --- Card number auto-format (spaces every 4 digits) ---
    const cardInput = document.getElementById('card-number');
    if (cardInput) {
        cardInput.addEventListener('input', function() {
            try {
                let v = this.value.replace(/\D/g, '').substring(0, 16);
                this.value = v.replace(/(\d{4})(?=\d)/g, '$1 ');
            } catch(e) { console.error('[Card Format]', e); }
        });
    }

    // --- Expiry auto-format (MM/YY) ---
    const expiryInput = document.getElementById('expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function() {
            try {
                let v = this.value.replace(/\D/g, '').substring(0, 4);
                if (v.length >= 3) v = v.substring(0,2) + '/' + v.substring(2);
                this.value = v;
            } catch(e) { console.error('[Expiry Format]', e); }
        });
    }

    // --- CVV restrict to digits ---
    const cvvInput = document.getElementById('cvv');
    if (cvvInput) {
        cvvInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').substring(0, 4);
        });
    }

    // --- Form submit validation ---
    form.addEventListener('submit', function(e) {
        try {
            let hasErrors = false;
            const requiredFields = ['email', 'fname', 'lname', 'address', 'city', 'zip'];

            // Add credit card fields if credit card is selected
            const paymentVal = document.getElementById('paymentMethod');
            if (paymentVal && paymentVal.value === 'credit_card') {
                requiredFields.push('card-name', 'card-number', 'expiry', 'cvv');
            }

            requiredFields.forEach(id => {
                const input = document.getElementById(id);
                if (!input) return;
                const val = input.value.trim();
                if (!val) {
                    showFieldError(input, 'This field is required.');
                    hasErrors = true;
                } else if (rules[id] && !rules[id].test(val)) {
                    showFieldError(input, rules[id].msg);
                    hasErrors = true;
                }
            });

            if (hasErrors) {
                e.preventDefault();
                console.log('[Checkout] Form submission blocked due to validation errors');
                if (typeof window.showErrorToast === 'function') {
                    window.showErrorToast('Please fix the errors in the form before submitting.', 'warning');
                }
                // Scroll to first error
                const firstErr = form.querySelector('.checkout-field-error');
                if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                console.log('[Checkout] Form validation passed - submitting');
            }
        } catch(err) {
            console.error('[Checkout] Validation error:', err);
        }
    });

    console.log('%c✅ Checkout validation ready', 'color:#27ae60;');
})();
</script>

</body>
</html>
