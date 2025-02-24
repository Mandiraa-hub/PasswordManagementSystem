<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Vault Sidebar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
        /* General Styles */
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background: #1e1e1e; /* Dark background */
            color: #e0e0e0; /* Light text color */
        }

        .sidebar {
            width: 350px;
            background: #282828;
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

        .sidebar a:hover {
            background: #3a3a3a;
            color: #ffffff;
        }

        .sidebar a i {
            margin-right: 12px;
            font-size: 1.2em;
        }
        p{
            margin: 0;
            padding: 10px;
            font-size: 1.1em;
            color: #a7f3d0;
            font-weight: bold;
            border-top: 1px solid #10b981;
            border-bottom: 1px solid #10b981;

        }
       

    </style>
</head>
<body>
    <div class="sidebar">
        <a href="store_password.php"><i class="fas fa-save"></i> Store Password</a>
        <a href="view_passwords.php"><i class="fas fa-eye"></i> View Saved Passwords</a>
        <div class="Tools">
        <p><i class="fa-solid fa-screwdriver-wrench"></i>Tools</p>
        <a href="generate_password.php"><i class="fas fa-key"></i> Password Generator</a>
        <a href="strength_checker.php"><i class="fa-solid fa-check-to-slot"></i> Strength Checker</a>
        </div>
    </div>
</body>
</html>