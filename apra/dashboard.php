<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user info from session
$user_name = htmlspecialchars($_SESSION['username']);
$profile_picture = isset($_SESSION['profile_picture']) ? htmlspecialchars($_SESSION['profile_picture']) : 'path/to/default/profile.png'; // Default image
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

        /* Header Styling */
        .header {
            width: 100%;
            background: #282828; /* Darker header */
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            height: 60px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            z-index: 100;
        }

        .header .logo {
            color: #10b981;
            font-size: 1.8em;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: none;
        }

        .header .profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header .profile img {
            border-radius: 50%;
            width: 45px;
            height: 45px;
            object-fit: cover;
            border: 2px solid #10b981;
        }

        .header .profile .profile-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 2px;
        }

        .header .profile .profile-info span {
            color: #a7f3d0;
            font-size: 1em;
            font-weight: bold;
            white-space: nowrap;
        }

        .header .profile .profile-info a {
            color: #d1d5db;
            font-size: 0.9em;
            text-decoration: none;
            transition: color 0.3s ease;
            font-weight: bold;
        }

        .header .profile .profile-info a:hover {
            color: #10b981;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 350px;
            background: #282828; /* Dark sidebar */
            color: #10b981;
            height: calc(100vh - 60px);
            display: flex;
            flex-direction: column;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 60px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            font-size: 1.1em;
            text-decoration: none;
            color: #a7f3d0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar a i {
            margin-right: 12px;
            font-size: 1.2em;
        }

        .sidebar a:hover {
            background: #3a3a3a; /* Darker hover effect */
            color: #ffffff;
            transform: translateX(5px);
        }

        /* Content Styling */
        .content {
            flex: 1;
            display: flex;
            justify-content: center; /* Center glass card horizontally */
            align-items: center; /* Center glass card vertically */
            background: #1e1e1e;
            margin-left: 260px; /* Adjust based on sidebar width */
            padding: 50px;
            position: relative;
        }

        .glass-card {
            background: rgba(27, 31, 35, 0.85);
            border-radius: 15px;
            padding: 40px 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.7);
            max-width: 600px;
            width: 100%;
            text-align: center;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column; /* Stack elements vertically */
            align-items: center; /* Center content horizontally */
            justify-content: center; /* Center content vertically */
        }

        .glass-card:hover {
            transform: scale(1.02); /* Zoom effect on hover */
        }

        .action-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: center; /* Center action buttons */
            gap: 10px; /* Space between buttons */
        }

        .action-buttons a {
            display: inline-block;
            background: #10b981; /* Teal background */
            color: #0f0f0f; /* Text color */
            border-radius: 5px;
            padding: 10px 20px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1em;
            font-weight: bold;
        }

        .action-buttons a:hover {
            background: #059669; /* Darker teal on hover */
        }

        /* Responsive Styles */
        @media (max-width: 1024px) {
            .sidebar {
                width: 220px;
            }

            .content {
                margin-left: 220px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
                padding: 15px;
            }

            .content {
                margin-left: 200px;
            }
        }

        @media (min-width: 1440px) {
            .glass-card {
                max-width: 800px;
                padding: 50px;
            }
            .content {
                padding: 60px;
            }
        }
    </style>
</head>
<body>
<div class="header">
    <a href="dashboard.php" class="logo">Password Vault</a>
    <div class="profile">
        <div class="profile-info">
            <a href="profile.php">
                <i class="fas fa-user-circle"></i> 
                <span><?php echo "Welcome"." ". $user_name; ?></span>
            </a>
        </div>
    </div>
</div>
    <div class="sidebar">
        <a href="generate_password.php"><i class="fas fa-key"></i> Generate Password</a>
        <a href="store_password.php"><i class="fas fa-save"></i> Store Password</a>
        <a href="view_passwords.php"><i class="fas fa-eye"></i> View Saved Passwords</a>
        <a href="search_password.php"><i class="fas fa-search"></i> Search Your Passwords</a>
    </div>
    <div class="content">
        <div class="glass-card">
            <h2 class="text-3xl font-bold text-white mb-4">Welcome to Password Vault</h2>
            <p class="text-center text-gray-300 mb-6">
                Select an option from the menu to get started!
            </p>
        </div>
    </div>

    <script>
        // Remove the notification script as it's no longer needed
    </script>
</body>
</html>