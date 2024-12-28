<?php
session_start();
$message = '';
$messageClass = '';
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars($_POST['email']);  
    $password = $_POST['password'];  
    try {
        $connection = new mysqli('localhost', 'root', '', 'pms');

        // Check connection
        if ($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }

        // Prepare and bind
        $stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Login success
                $_SESSION['user_id'] = $user['user_id']; // Assign user ID to session
                $_SESSION['username'] = $user['username']; // Store username in session
                $_SESSION['email'] = $user['email']; // Store email in session
                $_SESSION['masterPassword'] = $user['password']; // Store password in session
                 // Store session information
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $session_query = "INSERT INTO user_sessions (user_id, ip_address, user_agent) VALUES (?, ?, ?)";
            $session_stmt = $connection->prepare($session_query);
            $session_stmt->bind_param("iss", $user['user_id'], $ip_address, $user_agent);
            $session_stmt->execute();
            $session_stmt->close();

                // Set cookies if "Remember Me" is checked
                if (isset($_POST['remember'])) {
                    // Set cookies for 30 days (86400 seconds * 30 days)
                    setcookie('email', $user['email'], time() + (86400 * 30), "/");
                    setcookie('user_id', $user['user_id'], time() + (86400 * 30), "/"); 
                }

                header('Location: dashboard.php'); // Redirect to dashboard after login
                exit();
            } else {
                $message = 'Invalid email or password';
                $messageClass = 'error';
            }
        } else {
            $message = 'Invalid email or password';
            $messageClass = 'error';
        }

        // Close the statement and connection
        $stmt->close();
        $connection->close();
    } catch (Exception $e) {
        $message = 'Database Error: ' . $e->getMessage();
        $messageClass = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | PassVault</title>
    <link rel="stylesheet" href="styles.css">
    <style>
   * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: 'Arial', sans-serif;
    background-color: #1A202C; /* Dark Blue background */
    color: #E2E8F0; /* Light Gray text */
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.login-container {
    background-color: #2D3748; /* Slightly lighter Dark Blue */
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.3);
    max-width: 350px;
    width: 100%;
}
h2 {
    text-align: center;
    color: #32CD32; /* Neon Green heading */
    margin-bottom: 20px;
}
.input-group {
    margin-bottom: 15px;
    position: relative;
}
input[type="email"], input[type="password"] {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    border: 1px solid #4A5568; /* Gray border */
    border-radius: 5px;
    background-color: #1A202C; /* Dark background */
    color: #E2E8F0; /* Light Gray text */
    outline: none;
}
input[type="email"]:focus, input[type="password"]:focus {
    border-color: #32CD32; /* Neon Green focus */
}
.checkbox-group {
    display: flex;
    align-items: center;
}
.checkbox-group label {
    margin-left: 5px;
    font-size: 14px;
    color: #E2E8F0; /* Light Gray text */
}
.login-btn {
    width: 100%;
    padding: 12px;
    background-color: #32CD32; /* Neon Green button */
    color: #1A202C; /* Dark text */
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
.login-btn:hover {
    background-color: #28A745; /* Darker green on hover */
}
.forgot-password {
    text-align: center;
    margin-top: 10px;
}
.forgot-password a {
    color: #32CD32; /* Neon Green link */
    text-decoration: none;
}
.forgot-password a:hover {
    text-decoration: underline;
}
.create-account {
    text-align: center;
    margin-top: 20px;
}
.create-account a {
    color: #32CD32; /* Neon Green link */
    text-decoration: none;
}
.create-account a:hover {
    text-decoration: underline;
}
/* Transition for smooth hover effects */
button, input {
    transition: background-color 0.3s, border-color 0.3s;
}
.toggle-password {
            position: absolute;
            top: 65%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #CBD5E0; /* Lighter gray */
        }
     </style>
</head>
<body>
    <fieldset>
        <div class="login-container">
            <h2>Login</h2>
            <form action="login.php" method="post">
                <div class="input-group">
                    <label for="email"></label>
                    <input type="email" id="email" name="email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <label for="password"></label>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <span class="toggle-password" onclick="togglePasswordVisibility('password')">👁️</span>
                </div>
                <div class="input-group checkbox-group">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember Me</label>
                </div>
                <div id="message" class="message <?php echo $messageClass; ?>" style="display: <?php echo $message ? 'block' : 'none'; ?>; color: red;">
                <?php echo $message; ?>
            </div>
                <button type="submit" class="login-btn">Login</button>
                <p class="create-account">
                    Don't have an account? <a href="register.php">Create Account</a>
                </p>
            </form>
        </div>
    </fieldset>
    <script>
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
            field.setAttribute('type', type);
        }
    </script>
</body>
</html>