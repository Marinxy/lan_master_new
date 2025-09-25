<?php
require_once 'user_auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Logout the user
logoutUser();

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to main page with success message
header('Location: index.php?logout=success');
exit;
?>
