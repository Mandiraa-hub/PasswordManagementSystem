<?php
// Check if the user is logged in
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit();
// }
session_start();
// Get user info from session
$user_name = htmlspecialchars($_SESSION['username']);?>
<style>
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
        position: relative; /* For dropdown positioning */
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

    /* Dropdown Menu Styles */
    .dropdown {
        display: none;
        position: absolute;
        top: 23px;
        background-color: #333; /* Dark background for dropdown */
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        min-width: 180px;
        z-index: 200;
        opacity: 0; /* Hidden by default */
        transition: opacity 0.2s ease, visibility 0.2s ease;
        visibility: hidden; /* Hidden by default */
    }

    .dropdown a {
        color: #d1d5db;
        padding: 12px 16px;
        text-decoration: none;
        display: flex;
        align-items: center;
        border-radius: 8px; /* Rounded corners */
        transition: background 0.3s ease;
    }

    .dropdown a:hover {
        background-color: #444; /* Darker hover effect */
    }

    .dropdown a i {
        margin-right: 10px; /* Space between icon and text */
    }

    /* Show the dropdown on hover */
    .profile:hover .dropdown {
        display: block;
        opacity: 1; /* Fade in effect */
        visibility: visible; /* Make it visible */
    }
</style>

<div class="header">
    <a href="dashboard.php" class="logo">PassVault</a>
    <div class="profile">
        <div class="profile-info">
            <a href="profile.php">
                <i class="fas fa-user-circle"></i> 
                <span><?php echo "Welcome " . $user_name; ?></span>
            </a>
        </div>
        <div class="dropdown">
            <a href="profile.php"><i class="fas fa-eye"></i> View Profile</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</div>