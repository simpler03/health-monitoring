<?php
/**
 * Main Index - Redirect to Dashboard or Login
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect based on authentication status
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit;
?>
