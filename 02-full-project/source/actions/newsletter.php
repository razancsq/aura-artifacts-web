<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check for duplicate
        $stmt = $conn->prepare("SELECT newsletter_id FROM newsletter WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows > 0) {
            $_SESSION['newsletter_msg'] = "You are already subscribed to our newsletter!";
            $_SESSION['newsletter_type'] = "error";
        } else {
            $insert = $conn->prepare("INSERT INTO newsletter (email) VALUES (?)");
            $insert->bind_param("s", $email);
            if ($insert->execute()) {
                $_SESSION['newsletter_msg'] = "Thank you for subscribing!";
                $_SESSION['newsletter_type'] = "success";
            } else {
                $_SESSION['newsletter_msg'] = "An error occurred. Please try again.";
                $_SESSION['newsletter_type'] = "error";
            }
        }
    } else {
        $_SESSION['newsletter_msg'] = "Please enter a valid email address.";
        $_SESSION['newsletter_type'] = "error";
    }
}
header("Location: ../index#newsletter");
exit();
?>
