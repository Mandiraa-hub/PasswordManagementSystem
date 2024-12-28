<?php
session_start();  // Start the session

// Check if the user is logged in by verifying session variables
if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect to the login page
    header('Location: login.php');
    exit();
}

// Database connection
$connection = new mysqli('localhost', 'root', '', 'pms');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Retrieve the password ID from the query string parameter (QSP)
$password_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($password_id > 0) {
    // Delete the password from the database
    $stmt = $connection->prepare("DELETE FROM password WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $password_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $message = 'Password deleted successfully!';
    } else {
        $message = 'Failed to delete the password. Please try again.';
    }
    $stmt->close();
} else {
    $message = 'Invalid password ID.';
}

$connection->close();

// Redirect back to the view passwords page with a message
header("Location: view_passwords.php?message=" . urlencode($message));
exit();
?>