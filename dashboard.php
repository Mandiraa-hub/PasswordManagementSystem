
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_password'])) {
        $password_length = intval($_POST['password_length']);
        $include_numbers = isset($_POST['include_numbers']);
        $include_uppercase = isset($_POST['include_uppercase']);
        $include_lowercase = isset($_POST['include_lowercase']);
        $include_special_chars = isset($_POST['include_special_chars']);

        function generatePassword($length, $numbers, $uppercase, $lowercase, $specialChars) {
            $chars = '';
            if ($numbers) $chars .= '0123456789';
            if ($uppercase) $chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            if ($lowercase) $chars .= 'abcdefghijklmnopqrstuvwxyz';
            if ($specialChars) $chars .= '!@#$%^&*()_-+=<>?';
            if (empty($chars)) return 'Error: No character sets selected!';
            $password = '';
            for ($i = 0; $i < $length; $i++) {
                $password .= $chars[random_int(0, strlen($chars) - 1)];
            }
            return $password;
        }

        $generated_password = generatePassword($password_length, $include_numbers, $include_uppercase, $include_lowercase, $include_special_chars);
    }

    if (isset($_POST['store_password'])) {
        $website = htmlspecialchars($_POST['website']);
        $username = htmlspecialchars($_POST['username']);
        $password = htmlspecialchars($_POST['password']);
        $stored_message = "Password for '$website' (Username: $username) stored successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Vault Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* General Styles */
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(145deg, #1f1c2c, #928dab);
            color: #f1f5f9;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 300px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
            transition: width 0.3s;
            overflow: hidden;
        }

        .sidebar:hover {
            width: 350px;
        }

        .sidebar h2 {
            color: #38bdf8;
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.8em;
        }

        .sidebar a {
            display: block;
            padding: 15px;
            margin: 10px 0;
            font-size: 1.2em;
            text-decoration: none;
            color: #f1f5f9;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.15);
            transition: all 0.3s ease;
        }

        .sidebar a:hover {
            background: rgba(56, 189, 248, 0.3);
            color: #ffffff;
            transform: translateX(10px);
        }

        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 20px;
            margin: 30px auto;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            color: #f1f5f9;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #f1f5f9;
        }

        .form-group input,
        .form-group button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 8px;
            color: #f1f5f9;
            background: rgba(255, 255, 255, 0.1);
        }

        .form-group button {
            background-color: #2563eb;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-group button:hover {
            background-color: #1d4ed8;
        }

        .message {
            padding: 15px;
            margin-top: 20px;
            background: rgba(56, 189, 248, 0.15);
            backdrop-filter: blur(5px);
            color: #ffffff;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Password Vault</h2>
        <a href="#generate-password"><i class="fas fa-key"></i> Generate Password</a>
        <a href="#store-password"><i class="fas fa-save"></i> Store Password</a>
        <a href="#view-passwords"><i class="fas fa-eye"></i> View Saved Passwords</a>
        <a href="#logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <div class="content">
        <?php if (!empty($generated_password)): ?>
            <div class="message">
                <strong>Generated Password:</strong> <?php echo htmlspecialchars($generated_password); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($stored_message)): ?>
            <div class="message"><?php echo htmlspecialchars($stored_message); ?></div>
        <?php endif; ?>

        <div class="glass-card" id="generate-password">
            <h2>Generate Password</h2>
            <form method="post">
                <div class="form-group">
                    <label for="password_length">Password Length:</label>
                    <input type="number" id="password_length" name="password_length" value="12" min="4" max="64" required>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="include_numbers" checked> Include Numbers</label>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="include_uppercase" checked> Include Uppercase</label>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="include_lowercase" checked> Include Lowercase</label>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="include_special_chars" checked> Include Special Characters</label>
                </div>
                <div class="form-group">
                    <button type="submit" name="generate_password">Generate Password</button>
                </div>
            </form>
        </div>

        <div class="glass-card" id="store-password">
            <h2>Store Password</h2>
            <form method="post">
                <div class="form-group">
                    <label for="website">Website:</label>
                    <input type="text" id="website" name="website" required>
                </div>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="store_password">Store Password</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

