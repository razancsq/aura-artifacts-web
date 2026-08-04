<?php
// ============================================================
// wishlist_add.php - Add product to wishlist
// Uses prepared statements for database security
// ============================================================
require_once '../config.php';
if (!isset($_SESSION['customer_id'])) {
    header("Location: ../login");
    exit();
}
$cid = (int)$_SESSION['customer_id'];
$pid = intval($_GET['id'] ?? 0);

if ($pid > 0) {
    // Prepared statement: check if already in wishlist
    $stmt = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE customer_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $cid, $pid);
    $stmt->execute();
    $check = $stmt->get_result();
    if ($check->num_rows == 0) {
        $stmt->close();
        // Prepared statement: add to wishlist
        $stmt = $conn->prepare("INSERT INTO wishlist (customer_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $cid, $pid);
        $stmt->execute();
    }
    $stmt->close();
}
header("Location: ../product-detail.php?id=" . $pid . "&wishlist=success");
exit();
?>
