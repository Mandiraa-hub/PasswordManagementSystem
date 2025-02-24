<?php
session_start(); // Ensure the session is started

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not authenticated
    exit;
}
include 'header.php'; 
include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Vault Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
        /* General Styles */
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background: #1e1e1e; /* Dark background */
            color: #e0e0e0; /* Light text color */
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }
        /* Content Styling */
        .content {
            flex: 1;
            display: flex;
            justify-content: center; /* Center glass card horizontally */
            align-items: center; /* Center glass card vertically */
            background: #1e1e1e;
            margin-left: 350px; /* Adjust based on sidebar width */
            padding: 50px;
            position: relative;
        }
        .glass-card 
        {
            background: rgba(27, 31, 35, 0.85);
            border-radius: 15px;
            padding: 40px 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.7);
            max-width: 600px;
            width: 100%;
            text-align: center;
            display: flex;
            flex-direction: column; /* Stack elements vertically */
            align-items: center; /* Center content horizontally */
            justify-content: center; /* Center content vertically */
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="glass-card">
            <h2 class="text-3xl font-bold text-white mb-4">Welcome to Password Vault</h2>
            <p class="text-center text-gray-300 mb-6">
                Select an option from the menu to get started!
            </p>
        </div>
    </div>
</body>
</html>