<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$connection = new mysqli('localhost', 'root', '', 'pms');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Get user details from the database
$user_id = $_SESSION['user_id'];
$query = "SELECT username, email, created_at FROM users WHERE user_id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

// Fetch session information
$sessions_query = "SELECT ip_address, user_agent, timestamp FROM user_sessions WHERE user_id = ? ORDER BY timestamp DESC";
$sessions_stmt = $connection->prepare($sessions_query);
$sessions_stmt->bind_param("i", $user_id);
$sessions_stmt->execute();
$sessions_result = $sessions_stmt->get_result();

$sessions = [];
while ($row = $sessions_result->fetch_assoc()) {
    $sessions[] = $row;
}

$sessions_stmt->close();
$connection->close();

// Function to get OS logo based on user agent
function getOSLogo($user_agent) {
    if (preg_match('/android/i', $user_agent)) {
        return 'android.png';
    } elseif (preg_match('/linux/i', $user_agent)) {
        return 'linux.png';
    } elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
        return 'mac.png';
    } elseif (preg_match('/windows|win32/i', $user_agent)) {
        return 'windows.png';
    } else {
        return 'unknown.png';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Secure Vault</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #1A202C; /* Dark background */
            color: #E2E8F0; /* Light Gray text */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .profile-container {
            background-color: #2D3748; /* Slightly lighter background */
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
        }
        h2 {
            text-align: center;
            color: #32CD32; /* Neon Green heading */
            margin-bottom: 20px;
        }
        .profile-item {
            margin-bottom: 15px;
            font-size: 18px;
        }
        .profile-item span {
            font-weight: bold;
            color: #A0AEC0; /* Gray for labels */
        }
        .back-button {
            display: block;
            width: 100%;
            text-align: center;
            background-color: #32CD32; /* Neon Green */
            color: #1A202C; /* Dark text */
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            text-decoration: none;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #28A745; /* Darker Green */
        }
        .sessions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .sessions-table th, .sessions-table td {
            padding: 10px;
            border: 1px solid #444;
            text-align: left;
        }
        .sessions-table th {
            background-color: #32CD32; /* Neon Green */
            color: #1A202C; /* Dark text */
        }
        .sessions-table tr:nth-child(even) {
            background-color: #2a2a2a; /* Darker alternating row color */
        }
        .sessions-table tr:hover {
            background-color: #444; /* Hover effect */
        }
        .os-logo {
            width: 20px;
            height: 20px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>Your Profile</h2>
        <div class="profile-item">
            <span>Username:</span> <?php echo htmlspecialchars($user['username']); ?>
        </div>
        <div class="profile-item">
            <span>Email:</span> <?php echo htmlspecialchars($user['email']); ?>
        </div>
        <div class="profile-item">
            <span>Joined On:</span> <?php echo htmlspecialchars($user['created_at']); ?>
        </div>
        <h2>Session Information</h2>
        <table class="sessions-table">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>IP Address</th>
                    <th>User Agent</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sessions as $index => $session): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($session['ip_address']); ?></td>
                        <td><?php echo htmlspecialchars($session['user_agent']); ?></td>
                        <td><?php echo htmlspecialchars($session['timestamp']); ?></td>
                        </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="dashboard.php" class="back-button">Back to Dashboard</a>
    </div>
</body>
</html>