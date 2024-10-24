<?php
$message = '';
$messageClass = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $connection = new mysqli('localhost', 'root', '', 'pms');

        // Check connection
        if ($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }

        // Prepare and bind for inserting the new user
        $stmt = $connection->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $hashedPassword);

        // Execute the query
        if ($stmt->execute()) {
            $message = 'Registration successful! Please login.';
            $messageClass = 'success';
        } else {
            $message = 'Error: ' . $stmt->error;
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
    <title>Register | Secure Vault</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="register-container">
        <h2>Register</h2>
        <div id="message" class="message <?php echo htmlspecialchars($messageClass); ?>" style="display: <?php echo $message ? 'block' : 'none'; ?>;">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <form action="register.php" method="post">
            <div class="input-group">
                <input type="email" id="email" name="email" placeholder="Email" required>
            </div>
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="register-btn">Register</button>
            <p class="login-link">Already have an account? <a href="2.login.php">Login</a></p>
        </form>
    </div>
</body>
</html>
