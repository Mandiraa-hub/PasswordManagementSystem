<?php
$message = '';
$messageClass = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize user inputs
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];

    // Validate that passwords match
    if ($password != $confirmPassword) {
        $message = "Passwords do not match";
        $messageClass = 'error';
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Connect to the MySQL database
        try {
            $connection = new mysqli('localhost', 'root', '', 'pms');

            // Check connection
            if ($connection->connect_error) 
            {
                die("Connection failed: " . $connection->connect_error);
            }

            // Prepare and bind
            $stmt = $connection->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashedPassword);

            // Execute the statement
            if ($stmt->execute()) {
                $message = 'Signup success';
                $messageClass = 'success';
            } else {
                $message = 'Signup failed';
                $messageClass = 'error';
            }

            // Close the statement and connection
            $stmt->close();
            $connection->close();
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageClass = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - Password Management System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        
        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        
        button {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        button:hover {
            background-color: #0056b3;
        }
        
.message {
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 4px;
    display: none;
}

.message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <div id="message" class="message <?php echo $messageClass; ?>" style="display: <?php echo $message ? 'block' : 'none'; ?>;">
            <?php echo $message; ?>
        </div>
        <form action="register.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm-password" required>
            </div>
            <button type="submit">Register</button>
            <p class="signin-link">Already have an account? <a href="login.php">Sign in</a></p>
        </form>
    </div>
</body>
</html>
