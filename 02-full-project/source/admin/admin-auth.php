<?php
// ============================================================
// admin-auth.php - Admin Authentication Guard
// Include this at the top of every admin page (after config.php)
// Verifies the admin session is valid AND the admin still exists in DB
// If the admin was demoted (removed from admin table), they get kicked out
// ============================================================

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login");
    exit();
}

// Verify admin still exists in the database (handles demoted admins)
$_auth_stmt = $conn->prepare("SELECT admin_id FROM admin WHERE admin_id = ?");
$_auth_stmt->bind_param("i", $_SESSION['admin_id']);
$_auth_stmt->execute();
$_auth_result = $_auth_stmt->get_result();

if ($_auth_result->num_rows === 0) {
    // Admin no longer exists (was demoted) - destroy session and redirect
    $_auth_stmt->close();
    session_destroy();
    header("Location: admin-login");
    exit();
}
$_auth_stmt->close();
?>
