<?php
session_start();
include 'header.php';
include 'sidebar.php';
// Check if the user is logged in by verifying session variables
if (!isset($_SESSION['user_id']) || !isset($_SESSION['masterPassword'])) {
    header("Location: login.php"); // Redirect to login if not authenticated
    exit;
}

// Database connection
$connection = new mysqli('localhost', 'root', '', 'pms');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
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
$query = "SELECT p.id, p.category_id, c.name, p.website, p.username, p.password 
          FROM passwords p 
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

$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';

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
    overflow-x: hidden; /* Prevent horizontal scrolling */
}

.content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 10px;
    width: 100%; /* Full width */
    max-width: 900px;
    margin-bottom: 1px auto;
}

.glass-card {
    background: #1a1a1a; /* Dark card background */
    border-radius: 10px;
    padding: 2px ;
    width: 100%; /* Full width */
    margin-top: 200px;
    max-width: 900px;
    margin-right:500px ;
  
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
    padding: 5px;
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

.btn, .cbtn {
    display: inline-block;
    background-color: rgb(129, 186, 153); /* Navy green button */
    color: #000000; /* Black text */
    padding: 10px 20px;
    border-radius: 8px;
    text-align: center;
    font-size: 1rem;
    margin-top: 10px;
    text-decoration: none;
    transition: background-color 0.3s;
}

.btn:hover, .cbtn:hover {
    background-color: #27ae60; /* Darker navy green on hover */
}

.no-passwords {
    text-align: center;
    color: #e74c3c; /* Bright red for warnings */
    font-size: 1.2rem;
    margin-top: 20px;
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

.copy-btn {
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
    margin-left: 10px;
}

.copy-btn:hover {
    background-color: #0056b3;
}

.actions-cell {
    width: 150px; /* Adjust the width as needed */
}
    </style>
</head>
<body>
    <div class="content">
        <div class="glass-card">
        <h2>Stored Passwords</h2>
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if (!empty($allPasswords)): ?>
          <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Website</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th class="actions-cell">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allPasswords as $passwordData): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($passwordData['name']); ?></td>
                            <td><?php echo htmlspecialchars($passwordData['website']); ?></td>
                            <td><?php echo htmlspecialchars($passwordData['username']); ?></td>
                            <td class="password-cell">
                                <?php echo htmlspecialchars($passwordData['password']); ?>
                                <button class="copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($passwordData['password']); ?>')">Copy</button>
                            </td>
                            <td class="actions-cell">
                                <!-- Update and delete link that passes id -->
                                <button class="cbtn"> <a href="update_password.php?id=<?php echo $passwordData['id']; ?>" class="action-link">Update</a></button>
                                <button  class="cbtn"><a href="delete_password.php?id=<?php echo $passwordData['id']; ?>" class="action-link">Delete</a></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="no-passwords">No passwords stored yet.</div>

                <?php endif; ?>

<a href="store_password.php" class="btn">Store More Passwords</a>
</div>
</div>
<script>
// JavaScript to hide the message after 3 seconds
document.addEventListener('DOMContentLoaded', function() {
const messageElement = document.querySelector('.message');
if (messageElement) {
    setTimeout(() => {
        messageElement.style.display = 'none';
    }, 3000); // 3000 milliseconds = 3 seconds
}
});

function copyToClipboard(password) {
const textarea = document.createElement('textarea');
textarea.value = password;
document.body.appendChild(textarea);
textarea.select();
document.execCommand('copy');
document.body.removeChild(textarea);
alert('Password copied to clipboard');
}
</script>
</body>
</html>

