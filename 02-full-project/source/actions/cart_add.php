<?php
// ============================================================
// cart_add.php - Add product to cart
// Uses prepared statements for database security
// ============================================================
require_once '../config.php';
$pid = intval($_POST['product_id'] ?? 0);
$qty = intval($_POST['quantity'] ?? 1);

if ($pid > 0) {
    if (isset($_SESSION['customer_id'])) {
        $cid = (int)$_SESSION['customer_id'];
        // Prepared statement: check for existing cart
        $stmt = $conn->prepare("SELECT cart_id FROM cart WHERE customer_id = ?");
        $stmt->bind_param("i", $cid);
        $stmt->execute();
        $cart_res = $stmt->get_result();
        if ($cart_res->num_rows > 0) {
            $cart_id = $cart_res->fetch_assoc()['cart_id'];
        } else {
            $stmt->close();
            // Prepared statement: create new cart
            $stmt = $conn->prepare("INSERT INTO cart (customer_id) VALUES (?)");
            $stmt->bind_param("i", $cid);
            $stmt->execute();
            $cart_id = $conn->insert_id;
        }
        $stmt->close();
        
        // Prepared statement: check if item already in cart
        $stmt = $conn->prepare("SELECT cart_item_id, quantity FROM cart_item WHERE cart_id = ? AND product_id = ? AND gemstone_id IS NULL AND charm_id IS NULL AND preview_id IS NULL");
        $stmt->bind_param("ii", $cart_id, $pid);
        $stmt->execute();
        $item_res = $stmt->get_result();
        if ($item_res->num_rows > 0) {
            $item = $item_res->fetch_assoc();
            $new_qty = $item['quantity'] + $qty;
            $stmt->close();
            // Prepared statement: update quantity
            $stmt = $conn->prepare("UPDATE cart_item SET quantity = ? WHERE cart_item_id = ?");
            $stmt->bind_param("ii", $new_qty, $item['cart_item_id']);
            $stmt->execute();
        } else {
            $stmt->close();
            // Prepared statement: insert new cart item
            $stmt = $conn->prepare("INSERT INTO cart_item (cart_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $cart_id, $pid, $qty);
            $stmt->execute();
        }
        $stmt->close();
    } else {
        // Guest cart in session
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $pid && !isset($item['gemstone_id']) && !isset($item['charm_id']) && !isset($item['preview_id'])) {
                $item['quantity'] += $qty;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = ['product_id' => $pid, 'quantity' => $qty];
        }
    }
}
header("Location: ../product-detail.php?id=" . $pid . "&cart=success");
exit();
?>
