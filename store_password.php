<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$message = '';
include 'header.php';
include 'sidebar.php';
$connection = new mysqli('localhost', 'root', '', 'pms');

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

function encryptPassword($password, $masterPassword) {
    $key = hash('sha256', $masterPassword);
    $iv = openssl_random_pseudo_bytes(16);
    $encryptedPassword = openssl_encrypt($password, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encryptedPassword);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['store_password'])) {
    $category_id = $_POST['category'];
    $website = htmlspecialchars($_POST['website']);
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    $masterPassword = $_SESSION['masterPassword'];
    $encryptedPassword = encryptPassword($password, $masterPassword);

    $stmt = $connection->prepare("INSERT INTO password (category_id, website, username, password, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $category_id, $website, $username, $encryptedPassword, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();

    // Set success message
    $message = "Your password has been stored successfully!";
    echo "<script>window.onload = function() { showMessage('$message'); };</script>"; // Pass message to JavaScript
}

// Fetch categories for the dropdown
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
        /* General Styles */
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background: #1e1e1e;
            color: #e0e0e0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        .content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #1e1e1e;
            margin-left: 350px;
            padding: 50px;
            position: relative;
            margin-top: 60px;
        }

        .glass-card {
            background: rgba(27, 31, 35, 0.85);
            border-radius: 15px;
            padding: 40px 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.7);
            max-width: 700px; /* Increased max-width */
            width: 100%; /* Full width */
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            width: 100%; /* Full width for form elements */
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 1.2rem;
            color: #2ecc71;
        }

        .form-group input, .form-group select {
            width: 600px;/* Slightly wider */
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #2ecc71;
            background: rgba(46, 204, 113, 0.1);
            color:rgb(255, 13, 13);
            font-size: 1rem;
            justify-content:center;
            box-sizing: border-box; /* Ensure padding is included in width */
        }

        .form-group button {
            width: 200px;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: none;
            background: #2ecc71;
            color: #000;
            cursor: pointer;
            transition: background 0.3s ease;
            font-size: 1.1rem;
        }

        .form-group button:hover {
            background: #27ae60;
        }
         

        .message {
            display: none; /* Hide by default */
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 10px;
            color: #ffd700;
            text-align: center;
            font-size: 1.2rem;
            z-index: 1000;
        }

        .overlay {
            display: none; /* Hide by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>
    <script>
        function showMessage(message) {
            const overlay = document.createElement('div');
            overlay.className = 'overlay';
            document.body.appendChild(overlay);

            const messageBox = document.createElement('div');
            messageBox.className = 'message';
            messageBox.innerHTML = `&#128274; ${message}`;
            document.body.appendChild(messageBox);

            overlay.style.display = 'block';
            messageBox.style.display = 'block';

            overlay.onclick = function() {
                overlay.style.display = 'none';
                messageBox.style.display = 'none';
                document.body.removeChild(overlay);
                document.body.removeChild(messageBox);
            };

            setTimeout(function() {
                overlay.style.display = 'none';
                messageBox.style.display = 'none';
                document.body.removeChild(overlay);
                document.body.removeChild(messageBox);
            }, 5000); // Auto-hide after 5 seconds
        }
    </script>
</head>
<body>
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
                    <label for="username">Username or Email:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="text" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="store_password">Store Password</button>
                </div>
            </form>
            <a href="generate_password.php" class="store-btn">Generate Password</a>
        </div>
    </div>
</body>
</html>