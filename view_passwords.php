<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['masterPassword'])) {
    header("Location: login.php"); // Redirect to login if not authenticated
    exit;
}
include 'header.php'; 
include 'sidebar.php';
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
    background: #000000;
    color: #2ecc71;
    font-family: 'Arial', sans-serif;
    height: 100vh;
    margin: 0;
    display: flex;
    overflow-x: hidden; /* 🔹 Hides any horizontal scrolling */
}

/* Ensure the content does not overflow */
.content {
    flex: 1;
    padding: 40px;
    width: calc(100% - 250px); /* 🔹 Ensures content width adjusts properly */
    margin-left: 250px; /* 🔹 Keeps content aligned with sidebar */
    margin-top: 80px;
    min-height: 100vh;
    overflow-x: hidden; /* 🔹 Prevents extra horizontal scrollbar */
    overflow-y: auto;
}

/* Ensure the table does not overflow the glass card */
.glass-card {
    background: #1a1a1a;
    border-radius: 10px;
    padding: 20px;
    width: 100%;
    max-width: 1000px;
    margin: auto;
    position: relative;
    
}

/* Ensure tables are responsive without causing extra scrollbars */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    table-layout: auto; /* 🔹 Change to "auto" so column width adjusts dynamically */
}

/* Ensure table cells show full content */
th, td {
    word-wrap: break-word; /* 🔹 Allows text to wrap */
    overflow: hidden;
    text-overflow: ellipsis; /* 🔹 Prevents unnecessary cut-off */
    white-space: normal; /* 🔹 Ensures content wraps instead of being cut off */
    padding: 10px; /* 🔹 Increase padding for better readability */
    text-align: left;
    border: 1px solid #444;
    max-width: 300px; /* 🔹 Prevents excessive column stretching */
}

h2 {
    text-align: center;
    font-size: 1.8rem;
    margin-bottom: 20px;
}

th {
    background: #2ecc71; /* Navy green header */
    color: #000000; /* Black text */
    font-weight: bold;
}

tr:nth-child(even) {
    background: #2a2a2a; /* Darker alternating row color */
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
.search-bar {
            width: 100%;
            max-width: 500px;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #2ecc71;
            background: rgba(46, 204, 113, 0.1);
            color: #ffffff;
            font-size: 1rem;
}
    </style>
</head>
<body>
    <div class="content">
        <div class="glass-card">
        <h2>Stored Passwords</h2>

<input type="text" id="search-bar" class="search-bar" onkeyup="searchPasswords()" placeholder="Search by website or username">


            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if (!empty($allPasswords)): ?>
          <table id="passwordTable">
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

 // Function to filter passwords in the table
 function searchPasswords() {
            let input = document.getElementById("search-bar").value.toLowerCase();
            let table = document.getElementById("passwordTable");
            let rows = table.getElementsByTagName("tr");

            for (let i = 1; i < rows.length; i++) { // Start from 1 to skip header row
                let website = rows[i].getElementsByTagName("td")[1].textContent.toLowerCase();
                let username = rows[i].getElementsByTagName("td")[2].textContent.toLowerCase();
                if (website.includes(input) || username.includes(input)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
</script>
</body>
</html>
