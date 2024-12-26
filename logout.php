<?php
session_start(); // Start the session

// Unset all session variables
$_SESSION = array();

// This checks if the session is using cookies to store the session ID.This ensures the session cookie is removed securely, minimizing the risk of session hijacking or leftover session data in the user's browser.
if (ini_get("session.use_cookies")) {
    //Fetches the current configuration for session cookies, including: path, domain,secure,httponly(cookie is accessible only through HTTP (not JavaScript))
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear the "Remember Me" cookies if they exist
if (isset($_COOKIE['email'])) {
    setcookie('email', '', time() - 3600, "/"); // Expire the cookie
}
if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, "/"); // Expire the cookie
}

// Redirect to the login page
header('Location: login.php');
exit();
?>