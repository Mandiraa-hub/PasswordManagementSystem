<?php
//require('register.php');
$message = '';
$messageClass = '';

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
            error_log(print_r($user, true));
            // Verify password
            echo $user['password'];
           
            if (password_verify($password,$user['password'])) {
                $message = 'Login success';
                $messageClass = 'success';
                header('Location: dashboard.php');
                exit();
            } else {
                $message = 'Invalid email or password';
                $messageClass = 'error';
            }
        } else {
            $message = 'Invalid email or password';
            $messageClass = 'error';
        }
        echo $password;
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
    <title>Login | Secure Vault</title>
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
     </style>
</head>
<body>
    <fieldset>
    <div class="login-container">
        <h2>Login</h2>
        <div id="message" class="message <?php echo $messageClass; ?>" style="display: <?php echo $message ? 'block' : 'none'; ?>;">
            <?php echo $message; ?>
        </div>
        <form action="login.php" method="post">
            <div class="input-group">
                <label for="email"></label>
                <input type="email" id="email" name="email" placeholder="Email" required>
            </div>
            <div class="input-group">
                <label for="password"></label>
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            <div class="input-group checkbox-group">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember Me</label>
            </div>
            <button type="submit" class="login-btn">Login</button>
            <p class="create-account">
                Don't have an account? <a href="register.html">Create Account</a>
            </p>
        </form>
    </div>
    </fieldset>
</body>
</html>