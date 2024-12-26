<?php
session_start();

// Database connection
$connection = new mysqli('localhost', 'root', '', 'pms');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
//print_r($_SESSION);
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
    <title>Search Passwords</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        body {
            background: #000000; /* Black background */
            color: #2ecc71; /* Minimal green text */
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

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 1.2rem;
            color: #2ecc71; /* Minimal green for labels */
        }

        .form-group input,
        .form-group button {
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
            background: #2ecc71; /* Minimal green button */
            color: #000; /* Black text */
            border: none;
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

        .results {
            margin-top: 20px;
        }

        .results ul {
            list-style-type: none;
            padding: 0;
        }

        .results li {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #2ecc71; /* Minimal green border */
            border-radius: 5px;
            background: rgba(46, 204, 113, 0.1); /* Light green background for results */
            color: #2ecc71; /* Minimal green text */
        }

        .btn {
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

        .btn:hover {
            background-color: #27ae60; /* Darker green on hover */
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <div class="glass-card">
            <h2>Search Passwords</h2>
            <form action="search_password.php" method="post">
            <input type="text" id="search_query" name="search_query" placeholder="Search by website" required>
            <button type="submit" name="search_password">Search</button>
        </form>
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
            <div class="no-results">No results found</div>
        <?php endif; ?>
        
    </div>

          
</body>
</html>