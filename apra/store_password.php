<?php
$message = '';

session_start();  // Start the session

// Check if the user is logged in by verifying session variables
if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect to the login page
    header('Location: login.php');
    exit();
}


// Database connection
$connection = new mysqli('localhost', 'root', '', 'pms');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Encryption and decryption functions
function encryptPassword($password, $masterPassword) {
    $key = hash('sha256', $masterPassword);  // Create a 256-bit key
    $iv = openssl_random_pseudo_bytes(16);  // Generate a 16-byte IV
    $encryptedPassword = openssl_encrypt($password, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encryptedPassword);  // Store the IV with the encrypted password
}
//print_r($_SESSION);

// Handle password storage
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['store_password'])) {
    $category_id = $_POST['category'];
    $website = htmlspecialchars($_POST['website']);
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    // Encrypt the password with the master password from the session
    $masterPassword = $_SESSION['masterPassword'];
    $encryptedPassword = encryptPassword($password, $masterPassword);

    $stmt = $connection->prepare("INSERT INTO password (category_id, website, username, password, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $category_id, $website, $username, $encryptedPassword , $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}


$categories = [];
$result = $connection->query("SELECT * FROM categories");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Password</title>
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

        .form-group button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: none;
            background: #2ecc71; /* Minimal green button */
            color: #000; /* Black text */
            cursor: pointer;
            transition: background 0.3s ease;
            font-size: 1.1rem;
        }

        .form-group button:hover {
            background: #27ae60; /* Darker green on hover */
        }

        .message {
            margin-top: 20px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            color: #ffd700; /* Warning color */
            text-align: center;
            font-size: 1.2rem;
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
            <h2>Store Password</h2>
            <form action="store_password.php" method="post">
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="website">Website:</label>
                    <input type="text" id="website" name="website" required>
                </div>
                <div class="form-group">
                    <label for="username">Username or Email :</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <div style="position: relative;">
                        <input type="text" id="password" name="password" required style="padding-right: 30px;">
                        <i class="fas fa-eye toggle-password" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                    </div>
                    
                </div>
                <div class="form-group">
                    <button type="submit" name="store_password" onclick="alert('Your Password has been stored')">Store Password</button>
                </div>
            </form>
    
            <?php if (!empty($message)): ?>
                <div class="message">&#128274; <?php echo $message; ?></div>
            <?php endif; ?>
            <a href="generate_password.php" class="store-btn">Back to Generate Password</a>
        </div>
    </div>
</body>
</html>