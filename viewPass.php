<?php
session_start();

// Database connection
$connection = new mysqli('localhost', 'root', '', 'pms');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['masterPassword'])) {
    header("Location: login.php"); // Redirect to login if not authenticated
    exit;
}

$masterPassword = $_SESSION['masterPassword'];

// Decrypt function
function decryptPassword($encryptedPassword, $masterPassword) {
    $key = hash('sha256', $masterPassword);
    $data = base64_decode($encryptedPassword);
    if ($data === false || strlen($data) <= 16) {
        return "Invalid encrypted data"; // Error handling
    }
    $iv = substr($data, 0, 16);             // First 16 bytes are the IV
    $encryptedPassword = substr($data, 16); // Remaining bytes
    return openssl_decrypt($encryptedPassword, 'aes-256-cbc', $key, 0, $iv);
}

// Fetch all passwords for the logged-in user
$user_id = $_SESSION['user_id'];
$query = "SELECT p.category_id, c.name, p.website, p.username, p.password 
          FROM password p 
          INNER JOIN categories c ON p.category_id = c.id 
          WHERE p.user_id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$allPasswords = [];
while ($row = $result->fetch_assoc()) {
    $row['password'] = decryptPassword($row['password'], $masterPassword);
    $allPasswords[] = $row;
}

$stmt->close();
$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Passwords</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        a {
            text-decoration: none;
            color: #007bff;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your Stored Passwords</h1>
        <?php if (!empty($allPasswords)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Website</th>
                        <th>Username</th>
                        <th>Password</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allPasswords as $passwordData): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($passwordData['name']); ?></td>
                            <td><?php echo htmlspecialchars($passwordData['website']); ?></td>
                            <td><?php echo htmlspecialchars($passwordData['username']); ?></td>
                            <td><?php echo htmlspecialchars($passwordData['password']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No passwords stored yet.</p>
        <?php endif; ?>
        <p><a href="dashboard.php">Add New Password</a></p>
    </div>
</body>
</html>
