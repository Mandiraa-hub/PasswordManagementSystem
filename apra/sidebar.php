<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar - Green and Black Theme</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        /* Green and Black Theme */
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background: #0a0a0a; /* Deep black background */
            color: #e0e0e0; /* Light text for contrast */
        }
        .sidebar {
            background: #0a2a2a; /* Dark greenish-gray for sidebar */
            height: 100vh;
            padding: 20px;
            border-right: 2px solid #006400; /* Dark green border */
            box-shadow: 0 0 20px rgba(0, 128, 0, 0.1), 0 5px 15px rgba(0, 0, 0, 0.8); /* Green glow effect */
        }
        .sidebar-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 30px;
            color: #32cd32; /* Lime green title */
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            color: #a9a9a9; /* Gray for links */
            text-decoration: none;
            margin-bottom: 20px;
            font-size: 1.2rem;
            font-weight: bold;
            transition: color 0.3s, text-shadow 0.3s;
        }
        
        .sidebar-icon {
            margin-right: 10px;
            font-size: 1.5rem;
            color: #32cd32; /* Lime green icons */
            
        }
        .sidebar a:hover .sidebar-icon {
            text-shadow: 0 0 10px #32cd32, 0 0 20px #228b22;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="dashboard.php">
            <div class="sidebar-title">Password Vault</div>
        </a>
        <a href="generate_password.php">
            <span class="sidebar-icon">🔑</span> Generate Password
        </a>
        <a href="store_password.php">
            <span class="sidebar-icon">💾</span> Store Password
        </a>
        <a href="view_passwords.php">
            <span class="sidebar-icon">👁️</span> View Saved Passwords
        </a>
        <a href="search_password.php">
            <span class="sidebar-icon">🔍</span> Search Your Passwords
        </a>
    </div>
</body>
</html>