<?php
// logout.php
require_once '../config.php';
session_destroy();
setcookie('aura_email', '', time() - 3600, '/');
setcookie('aura_remember_token', '', time() - 3600, '/');
header("Location: ../index");
exit();
