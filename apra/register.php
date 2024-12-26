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
        // Hash the password using password_hash function that uses PASSWORD_dEFAULT parameter. This pamater states the bcrypt algorithm
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Connect to the MySQL database
        try {
            $connection = new mysqli('localhost', 'root', '', 'pms');

            // Check connection
            if ($connection->connect_error) 
            {
                die("Connection failed: " . $connection->connect_error);
            }

            // Prepare statement allows us to use placeholder for values and bind_param links values to the respective placeholder in prepare statement
            $stmt = $connection->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
           // "sss":data type of placeholders. Other options: s: String. i: Integer.d: Double (floating point).b: Blob (binary data).Since username,email and password are all string, SSS is used.
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1A202C; /* Dark background */
            color: #E2E8F0; /* Light text */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #2D3748; /* Dark grayish-blue */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #32CD32; /* Neon green */
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #CBD5E0; /* Lighter gray */
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #4A5568; /* Gray border */
            border-radius: 5px;
            background-color: #1A202C; /* Dark input background */
            color: #E2E8F0; /* Light input text */
            outline: none;
        }

        .form-group input:focus {
            border-color: #32CD32; /* Neon green focus */
            box-shadow: 0 0 5px rgba(50, 205, 50, 0.5);
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #32CD32; /* Neon green */
            color: #1A202C; /* Dark button text */
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #28A745; /* Slightly darker green on hover */
        }

        .signin-link {
            text-align: center;
            margin-top: 15px;
        }

        .signin-link a {
            color: #32CD32; /* Neon green link */
            text-decoration: none;
        }

        .signin-link a:hover {
            text-decoration: underline;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
            display: none;
        }

        .message.success {
            background-color: #38A169; /* Green success background */
            color: #E6FFFA; /* Light success text */
            border: 1px solid #2F855A;
        }

        .message.error {
            background-color: #E53E3E; /* Red error background */
            color: #FFF5F5; /* Light error text */
            border: 1px solid #C53030;
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
        <input type="text" id="username" name="username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required value="<?php echo isset($password) ? htmlspecialchars($password) : ''; ?>">
    </div>
    <div class="form-group">
        <label for="confirm-password">Confirm Password</label>
        <input type="password" id="confirm-password" name="confirm-password" required value="<?php echo isset($confirmPassword) ? htmlspecialchars($confirmPassword) : ''; ?>">
    </div>
    <button type="submit">Register</button>
    <p class="signin-link">Already have an account? <a href="login.php">Sign in</a></p>
</form>

    </div>
</body>
</html>