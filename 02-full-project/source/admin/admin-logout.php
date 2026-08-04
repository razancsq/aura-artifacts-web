<?php
require_once '../config.php';
unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_email']);
header("Location: admin-login");
exit();
