<?php
// ============================================================
// custom_cart_add.php - Add custom bracelet to cart
// Uses prepared statements for database security
// ============================================================
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['items']) && isset($data['totalPrice'])) {
    if (isset($_SESSION['customer_id'])) {
        $cid = (int)$_SESSION['customer_id'];
        
        $images_json = json_encode($data['items']);
        $total = floatval($data['totalPrice']);
        
        // Prepared statement: create bracelet preview
        $stmt = $conn->prepare("INSERT INTO braceletpreview (customer_id, total_price, custom_images) VALUES (?, ?, ?)");
        $stmt->bind_param("ids", $cid, $total, $images_json);
        $stmt->execute();
        $preview_id = $conn->insert_id;
        $stmt->close();
        
        // Prepared statement: get or create cart
        $stmt = $conn->prepare("SELECT cart_id FROM cart WHERE customer_id = ?");
        $stmt->bind_param("i", $cid);
        $stmt->execute();
        $cart_res = $stmt->get_result();
        if ($cart_res->num_rows > 0) {
            $cart_id = $cart_res->fetch_assoc()['cart_id'];
        } else {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO cart (customer_id) VALUES (?)");
            $stmt->bind_param("i", $cid);
            $stmt->execute();
            $cart_id = $conn->insert_id;
        }
        $stmt->close();
        
        // Prepared statement: add custom item to cart
        $stmt = $conn->prepare("INSERT INTO cart_item (cart_id, preview_id, quantity) VALUES (?, ?, 1)");
        $stmt->bind_param("ii", $cart_id, $preview_id);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode(['success' => true]);
    } else {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        
        $_SESSION['cart'][] = [
            'is_custom' => true,
            'items' => $data['items'],
            'price' => floatval($data['totalPrice']),
            'quantity' => 1
        ];
        echo json_encode(['success' => true]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}
?>
