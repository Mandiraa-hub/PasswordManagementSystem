<?php
session_start(); // Start the session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['masterPassword'])) {
    header("Location: login.php"); // Redirect to login if not authenticated
    exit;
}

$message = '';
include 'header.php';
include 'sidebar.php'; // Include the header
$connection = new mysqli('localhost', 'root', '', 'pms');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

function decryptPassword($encryptedPassword, $masterPassword) {
    $key = hash('sha256', $masterPassword);
    $data = base64_decode($encryptedPassword);
    
    if (strlen($data) < 16) {
        return "Invalid encrypted data"; // Handle invalid cases
    }

    $iv = substr($data, 0, 16);
    $encryptedPassword = substr($data, 16);
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
        $row['password'] = decryptPassword($row['password'], $_SESSION['masterPassword']);
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
            max-width: 600px;
            width: 100%;
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
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #2ecc71;
            background: rgba(46, 204, 113, 0.1);
            color: #ffffff;
            font-size: 1rem;
        }

        .form-group button {
            width: 100%;
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

        .results {
            margin-top: 20px;
            width: 100%;
        }

        .results h3 {
            color: #2ecc71; /* Header color */
            margin-bottom: 10px;
        }

        .results ul {
            list-style-type: none;
            padding: 0;
        }

        .results li {
            margin: 10px 0;
            padding: 15px; /* Increased padding for better spacing */
            border: 1px solid #2ecc71;
            border-radius: 5px;
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
            transition: background 0.3s ease; /* Smooth transition for hover effect */
        }

        .results li:hover {
            background: rgba(46, 204, 113, 0.2); /* Change background on hover */
        }

        .results strong {
            color: #ffffff; /* Make labels stand out */
        }
    </style>
</head>
<body>
<div class="content">
    <div class="glass-card">
        <h2>Search Passwords</h2>
        <form action="search_password.php" method="post">
            <div class="form-group">
                <input type="text" id="search_query" name="search_query" placeholder="Search by website" required>
            </div>
            <div class="form-group">
                <button type="submit" name="search_password">Search</button>
            </div>
        </form>

        <div class="results">
            <h3>Search Results</h3>
            <?php if (!empty($searchResults)): ?>
                <ul>
                    <?php foreach ($searchResults as $row): ?>
                        <li>
                            <strong>Website:</strong> <?php echo htmlspecialchars($row['website']); ?><br>
                            <strong>Username:</strong> <?php echo htmlspecialchars($row['username']); ?><br>
                            <strong>Password:</strong> <?php echo htmlspecialchars($row['password']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="no-results">No results found</div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>