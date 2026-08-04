<?php
// ============================================================
// wishlist_get.php - Get/Remove wishlist items (AJAX)
// Uses prepared statements for database security
// ============================================================
require_once '../config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['items' => [], 'logged_in' => false]);
    exit();
}

$cid = (int)$_SESSION['customer_id'];

// Handle delete
if (isset($_GET['remove'])) {
    $pid = intval($_GET['remove']);
    // Prepared statement: remove from wishlist
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE customer_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $cid, $pid);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
    exit();
}

// Get wishlist items using prepared statement
$stmt = $conn->prepare("SELECT p.product_id, p.name, p.base_price, p.image_url 
                     FROM wishlist w 
                     JOIN product p ON w.product_id = p.product_id 
                     WHERE w.customer_id = ? 
                     ORDER BY w.added_at DESC");
$stmt->bind_param("i", $cid);
$stmt->execute();
$res = $stmt->get_result();
$items = [];
while ($row = $res->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

echo json_encode(['items' => $items, 'logged_in' => true]);
