<?php
session_start();

// Database connection
$connection = new mysqli('localhost', 'root', '', 'pms');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
print_r($_SESSION);
// Define or fetch the master password
$masterPassword = $_SESSION['masterPassword'];

function decryptPassword($encryptedPassword, $masterPassword) {
    $key = hash('sha256', $masterPassword);// Ensure binary hash
    $data = base64_decode($encryptedPassword);
    
    // Validate decoded data length
    if (strlen($data) < 16) {
        return "Invalid encrypted data"; // Handle invalid cases
    }

    $iv = substr($data, 0, 16);  // Extract the IV
    $encryptedPassword = substr($data, 16); // Extract the encrypted part
    return openssl_decrypt($encryptedPassword, 'aes-256-cbc', $key, 0, $iv);
}

// Check if the search query is set
$searchResults = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_query'])) {
    $searchQuery = htmlspecialchars($_POST['search_query']);
    $stmt = $connection->prepare("SELECT * FROM password WHERE website LIKE ? AND user_id = ?");
    $searchTerm = "%$searchQuery%";
    $stmt->bind_param("si", $searchTerm, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['password'] = decryptPassword($row['password'], $masterPassword);
        $searchResults[] = $row;
    }
    $stmt->close();
}
$connection->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
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

        .header {
            text-align: center;
            margin-bottom: 20px;
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

        .no-results {
            text-align: center;
            margin: 20px 0;
            font-size: 1.2em;
            color: #555;
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
        <div class="header">
            <h1>Search Results</h1>
        </div>
        <?php if (!empty($searchResults)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Password</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($searchResults as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo $row['password']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-results">No passwords found for "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>".</p>
        <?php endif; ?>
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>