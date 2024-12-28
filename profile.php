<?php
session_start();

// Database connection
$connection = new mysqli('localhost', 'root', '', 'pms');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Initialize message variable
$message = '';

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate username (contains both characters and numbers)
function validateUsername($username) {
    return preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]+$/', $username);
}

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_account'])) {
        // Add a new account
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);

        if (empty($username) || empty($email)) {
            $message = "Both username and email are required.";
        } elseif (!validateUsername($username)) {
            $message = "Username must contain both letters and numbers.";
        } elseif (!validateEmail($email)) {
            $message = "Please enter a valid email address.";
        } else {
            // Proceed with adding the account
            $stmt = $connection->prepare("INSERT INTO users (username, email) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $email);

            if ($stmt->execute()) {
                $message = "Account added successfully!";
            } else {
                $message = "Error adding account: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif (isset($_POST['update_account'])) {
        // Update account
        $user_id = $_POST['user_id'];
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);

        if (empty($username) || empty($email)) {
            $message = "Both username and email are required.";
        } elseif (!validateUsername($username)) {
            $message = "Username must contain both letters and numbers.";
        } elseif (!validateEmail($email)) {
            $message = "Please enter a valid email address.";
        } else {
            // Proceed with updating the account
            $stmt = $connection->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $username, $email, $user_id);

            if ($stmt->execute()) {
                $message = "Account updated successfully!";
            } else {
                $message = "Error updating account: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif (isset($_POST['delete_account'])) {
        // Delete account
        $user_id = $_POST['user_id'];
        $stmt = $connection->prepare("DELETE FROM passwords WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $connection->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $message = "Account deleted successfully!";
        } else {
            $message = "Error deleting account: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Retrieve accounts
$result = $connection->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #1A202C, #2D3748);
        }
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>
    <div class="flex items-center justify-center min-h-screen">
        <div class="glass p-8 w-11/12 md:w-7/12 lg:w-7/12"> <!-- Set width to 70% -->
            <h2 class="text-2xl font-bold text-center text-white mb-4">User Profile Management</h2>

            <!-- Message Display -->
            <?php if ($message): ?>
                <div class="bg-red-500 text-white p-3 rounded-lg text-center mb-4"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Add Account Form -->
            <form method="POST" class="mb-6">
                <input type="text" name="username" placeholder="Enter Username" required class="w-full p-2 mb-4 rounded-lg bg-gray-700 text-white placeholder-gray-400">
                <input type="email" name="email" placeholder="Enter Email" required class="w-full p-2 mb-4 rounded-lg bg-gray-700 text-white placeholder-gray-400">
                <button type="submit" name="add_account" class="w-full p-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white">Add Account</button>
            </form>

            <!-- Accounts Table -->
            <table class="w-full mt-4 border-collapse">
                <thead>
                    <tr class="bg-gray-600 text-white">
                        <th class="p-2">ID</th>
                        <th class="p-2">Username</th>
                        <th class="p-2">Email</th>
                        <th class="p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $result->fetch_assoc()): ?>
                    <tr class="bg-gray-800 text-white">
                        <td class="p-2"><?php echo htmlspecialchars($user['user_id']); ?></td>
                        <td class="p-2"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td class="p-2"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="p-2">
                            <form method="POST" style="display:inline-block; width: 100%;">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <input type="text" name="username" placeholder="New Username" required class="w-full p-1 mb-2 rounded-lg bg-gray-600 text-white placeholder-gray-400">
                                <input type="email" name="email" placeholder="New Email" required class="w-full p-1 mb-2 rounded-lg bg-gray-600 text-white placeholder-gray-400">
                                <div class="flex justify-between">
                                    <button type="submit" name="update_account" class="w-1/2 p-1 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white">Update</button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <button type="submit" name="delete_account" class="w-1/2 p-1 rounded-lg bg-red-500 hover:bg-red-600 text-white" onclick="return confirm('Are you sure you want to delete this account?');">Delete</button>
                                    </form>
                                </div>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Back to Dashboard Button -->
            <form action="dashboard.php" method="get" class="mt-4">
                <button type="submit" class="w-full p-2 rounded-lg bg-gray-500 hover:bg-gray-600 text-white">Back to Dashboard</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php
$connection->close();
?>