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
    <title>View Passwords</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        body {
            background: #000000; /* Black background */
            color: #2ecc71; /* Navy green text */
            font-family: 'Arial', sans-serif; /* Clean font */
            height: 100vh;
            margin: 0;
            display: flex;
        }

        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .glass-card {
            background: #1a1a1a; /* Dark card background */
            border-radius: 10px;
            padding: 20px;
            width: 90%;
            max-width: 800px;
            margin: auto;
        }

        h2 {
            text-align: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #444; /* Slightly lighter border */
        }

        th {
            background: #2ecc71; /* Navy green header */
            color: #000000; /* Black text */
            font-weight: bold;
        }

        tr:nth-child(even) {
            background: #2a2a2a; /* Darker alternating row color */
        }

        tr:hover {
            background: #444; /* Hover effect */
        }

        .btn {
            display: block;
            background-color: #2ecc71; /* Navy green button */
            color: #000000; /* Black text */
            padding: 10px 20px;
            border-radius: 8px;
            text-align: center;
            font-size: 1rem;
            margin-top: 20px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #27ae60; /* Darker navy green on hover */
        }

        .no-passwords {
            text-align: center;
            color: #e74c3c; /* Bright red for warnings */
            font-size: 1.2rem;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <div class="glass-card">
            <h2>Stored Passwords</h2>
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
                <div class="no-passwords">No passwords stored yet.</div>
            <?php endif; ?>

            <a href="generate_password.php" class="btn">Back to Generate Password</a>
        </div>
    </div>
</body>
</html>