<?php
// ============================================================
// track_view.php - Track recently viewed products (Task 12)
// Called via AJAX from product-detail.php
// Author: Team Member
// ============================================================
require_once '../config.php';

if (isset($_POST['product_id'])) {
    $pid = (int)$_POST['product_id'];
    
    // Get existing cookie
    $viewed = [];
    if (isset($_COOKIE['recently_viewed'])) {
        $viewed = array_filter(array_map('intval', explode(',', $_COOKIE['recently_viewed'])));
    }
    
    // Remove if already in list, then prepend
    $viewed = array_values(array_filter($viewed, fn($v) => $v !== $pid));
    array_unshift($viewed, $pid);
    
    // Keep only last 5
    $viewed = array_slice($viewed, 0, 5);
    
    // Set cookie for 30 days
    setcookie('recently_viewed', implode(',', $viewed), time() + (30 * 24 * 3600), '/');
    echo json_encode(['success' => true]);
}
?>
