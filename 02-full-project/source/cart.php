<?php
require_once 'config.php';

// Handle AJAX actions (remove/update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    if ($action === 'remove') {
        if (isset($_POST['index'])) {
            $index = (int)$_POST['index'];
            if (isset($_SESSION['customer_id'])) {
                // If logged in, we need to remove from DB based on some ID.
                // For simplicity, we can just rebuild the session cart or delete by index.
                // Assuming we stored cart_item_id or just relying on session for now.
                // Best is to fetch from DB and delete the specific cart_item_id.
                if (isset($_POST['cart_item_id'])) {
                    $c_id = (int)$_POST['cart_item_id'];
                    // Prepared statement: delete cart item
                    $stmt = $conn->prepare("DELETE FROM cart_item WHERE cart_item_id = ?");
                    $stmt->bind_param("i", $c_id);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                if (isset($_SESSION['cart'][$index])) {
                    unset($_SESSION['cart'][$index]);
                    $_SESSION['cart'] = array_values($_SESSION['cart']);
                }
            }
        }
        echo json_encode(['success' => true]);
        exit();
    }
}

$is_logged = isset($_SESSION['customer_id']);
$cart_items = [];
$subtotal = 0;

if ($is_logged) {
    $cid = (int)$_SESSION['customer_id'];
    // Prepared statement: get cart
    $stmt = $conn->prepare("SELECT cart_id FROM cart WHERE customer_id = ?");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $cart_id = $res->fetch_assoc()['cart_id'];
        $stmt->close();
        // Prepared statement: get cart items with product details
        $stmt = $conn->prepare("SELECT ci.cart_item_id, ci.quantity, p.product_id, p.name, p.base_price, p.image_url, bp.preview_id, bp.custom_images, bp.total_price AS custom_price 
                FROM cart_item ci 
                LEFT JOIN product p ON ci.product_id = p.product_id
                LEFT JOIN braceletpreview bp ON ci.preview_id = bp.preview_id
                WHERE ci.cart_id = ?");
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        $items_res = $stmt->get_result();
        while ($row = $items_res->fetch_assoc()) {
            if ($row['preview_id']) {
                $item_total = $row['custom_price'] * $row['quantity'];
                $subtotal += $item_total;
                $cart_items[] = [
                    'cart_item_id' => $row['cart_item_id'],
                    'is_custom' => true,
                    'name' => 'Custom Bracelet Design',
                    'price' => $row['custom_price'],
                    'quantity' => $row['quantity'],
                    'total' => $item_total,
                    'image' => 'assets/images/custom_bracelet.png' // fallback image
                ];
            } else if ($row['product_id']) {
                $item_total = $row['base_price'] * $row['quantity'];
                $subtotal += $item_total;
                $cart_items[] = [
                    'cart_item_id' => $row['cart_item_id'],
                    'is_custom' => false,
                    'name' => $row['name'],
                    'price' => $row['base_price'],
                    'quantity' => $row['quantity'],
                    'total' => $item_total,
                    'image' => $row['image_url']
                ];
            }
        }
    }
} else {
    // Session cart
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            if (isset($item['is_custom']) && $item['is_custom']) {
                $item_total = $item['price'] * $item['quantity'];
                $subtotal += $item_total;
                $cart_items[] = [
                    'is_custom' => true,
                    'name' => 'Custom Bracelet Design',
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total' => $item_total,
                    'image' => 'assets/images/custom_bracelet.png'
                ];
            } else {
                $pid = intval($item['product_id']);
                // Prepared statement: get product for guest cart
                $stmt = $conn->prepare("SELECT name, base_price, image_url FROM product WHERE product_id = ?");
                $stmt->bind_param("i", $pid);
                $stmt->execute();
                $prod_res = $stmt->get_result();
                if ($prod_res && $prod = $prod_res->fetch_assoc()) {
                    $item_total = $prod['base_price'] * $item['quantity'];
                    $subtotal += $item_total;
                    $cart_items[] = [
                        'is_custom' => false,
                        'name' => $prod['name'],
                        'price' => $prod['base_price'],
                        'quantity' => $item['quantity'],
                        'total' => $item_total,
                        'image' => $prod['image_url']
                    ];
                }
            }
        }
    }
}

$shipping = 10.00;
$total = $subtotal > 0 ? $subtotal + $shipping : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Review your shopping cart at Aura Artifacts. View items, update quantities, and proceed to checkout.">
    <title>Aura Artifacts | Shopping cart</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .cart-item-img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        .cart-item-info { display: flex; align-items: center; gap: 15px; }
    </style>
</head>
<body>
    
    <?php include "includes/navbar.php"; ?>

    <main class="cart-container section">
        <h1 class="text-center">Your Shopping cart</h1>

        <div class="cart-wrapper">
            <div class="cart-items">
                <table class="cart-table">
                    <thead>
                    <tr>
                        <th>product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($cart_items)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 40px;">Your cart is empty</td></tr>
                    <?php else: ?>
                        <?php foreach ($cart_items as $index => $item): ?>
                        <tr>
                            <td data-label="product">
                                <div class="cart-item-info">
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-img" onerror="this.src='assets/images/ABOUT US.jpg'">
                                    <div>
                                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Price">$<?= number_format($item['price'], 2) ?></td>
                            <td data-label="Quantity"><?= $item['quantity'] ?></td>
                            <td data-label="Total">$<?= number_format($item['total'], 2) ?></td>
                            <td>
                                <button onclick="removeItem(<?= $index ?>, <?= $item['cart_item_id'] ?? 'null' ?>)" style="background: #ff6b6b; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Remove</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
                <a href="collections" class="cart-btn cart-btn-secondary" style="margin-top:20px; display:inline-block;">Continue Shopping</a>
            </div>

            <div class="cart-summary">
                <h3>order Summary</h3>
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
                <br>
                <?php if ($subtotal > 0): ?>
                    <a href="checkout" class="cart-btn cart-btn-primary cart-btn-full">Proceed to Checkout</a>
                <?php else: ?>
                    <button class="cart-btn cart-btn-primary cart-btn-full" disabled style="opacity:0.5; cursor:not-allowed;">Proceed to Checkout</button>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container text-center">
            <p>&copy; 2026 Aura Artifacts. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        function removeItem(index, cartItemId) {
            const formData = new URLSearchParams();
            formData.append('action', 'remove');
            formData.append('index', index);
            if (cartItemId) formData.append('cart_item_id', cartItemId);

            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    </script>
</body>
</html>
