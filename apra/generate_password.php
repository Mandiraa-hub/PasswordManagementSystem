<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    function checkPasswordStrength($password) {
        if (strlen($password) > 15 &&
            preg_match('/[0-9]/', $password) &&
            preg_match('/[A-Z]/', $password) &&
            preg_match('/[a-z]/', $password) &&
            preg_match('/[!@#$%^&*()]/', $password)) {
            return 'Strong'; // Only mark strong if all conditions are met
        } elseif (strlen($password) >= 8 &&
                  (preg_match('/[0-9]/', $password) ||
                   preg_match('/[A-Z]/', $password) ||
                   preg_match('/[!@#$%^&*()]/', $password))) {
            return 'Medium'; // Medium if some but not all conditions are met
        }
        return 'Weak'; // Mark as weak otherwise
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        body {
            background: #000000; /* Pure black background */
            height: 100vh;
            display: flex;
            font-family: 'Arial', sans-serif; /* Clean font */
            color: #2ecc71; /* Minimal green text */
            margin: 0;
        }

        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .glass-card {
            background: #1a1a1a; /* Dark card background */
            border-radius: 10px;
            padding: 30px;
            width: 90%;
            max-width: 800px;
            margin: auto;
        }

        h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 1.2rem;
            color: #2ecc71; /* Minimal green for labels */
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #2ecc71; /* Minimal green border */
            background: rgba(46, 204, 113, 0.1); /* Light green background for inputs */
            color: #ffffff; /* White text */
            font-size: 1rem;
        }

        button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: 2px solid #2ecc71; /* Minimal green border */
            background: #2ecc71; /* Minimal green button */
            color: #000; /* Black text */
            cursor: pointer;
            transition: background 0.3s ease;
            font-size: 1.1rem;
        }

        button:hover {
            background: #27ae60; /* Darker green on hover */
        }

        .generated-password {
            margin-top: 20px;
            font-size: 1.25rem;
            color: #2ecc71; /* Minimal green */
            text-align: center;
        }

        .store-btn {
            display: block;
            background-color: #2ecc71; /* Minimal green button */
            color: #000; /* Black text */
            padding: 10px 20px;
            border-radius: 8px;
            text-align: center;
            font-size: 1rem;
            margin-top: 20px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .store-btn:hover {
            background-color: #27ae60; /* Darker green on hover */
        }
       
        
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <div class="glass-card">
            <h2>Generate Password</h2>
            <form method="post">
                <div class="form-group">
                    <label for="password_length">Password Length:</label>
                    <input type="number" id="password_length" name="password_length" value="12" min="4" max="64" class="form-input">
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
                <button type="submit">Generate</button>
            </form>
            <?php if (isset($generated_password)): ?>
                <div class="generated-password">
                    <strong>Generated Password:</strong>
                    <span id="password-text"><?php echo htmlspecialchars($generated_password); ?></span>
                    <span class="copy-icon" onclick="copyToClipboard()">&#128203;</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function copyToClipboard() {
            const passwordText = document.getElementById('password-text').innerText;
            navigator.clipboard.writeText(passwordText).then(() => {
                alert('Password copied to clipboard!');
            }).catch(err => {
                alert('Failed to copy password.');
                console.error(err);
            });
        }
    </script>
</body>
</html>