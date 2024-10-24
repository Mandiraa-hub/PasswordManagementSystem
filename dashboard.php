<?php
session_start();  // Start the session

// Check if the user is logged in by verifying session variables
// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
//     // If the user is not logged in, redirect to the login page
//     header('Location: login.php');
//     exit();
// }


// Database connection
$connection = new mysqli('localhost', 'root','', 'pms');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Function to generate a random password based on user preferences
function generateRandomPassword($length = 12, $includeNumbers = true, $includeUppercase = true, $includeLowercase = true, $includeSpecialChars = true) {
    $characters = '';
    if ($includeNumbers) {
        $characters .= '0123456789';
    }
    if ($includeUppercase) {
        $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
    if ($includeLowercase) {
        $characters .= 'abcdefghijklmnopqrstuvwxyz';
    }
    if ($includeSpecialChars) {
        $characters .= '!@#$%^&*()';
    }

    $charactersLength = strlen($characters);
    $randomPassword = '';
    for ($i = 0; $i < $length; $i++) {
        $randomPassword .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomPassword;
}


// Handle password storage
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['store_password'])) {
    $category_id = $_POST['category'];
    $website = htmlspecialchars($_POST['website']);
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    $stmt = $connection->prepare("INSERT INTO password (category_id, website, username, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $category_id, $website, $username, $password);
    $stmt->execute();
    $stmt->close();
}

// Handle password search
$searchResults = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_password'])) {
    $searchQuery = htmlspecialchars($_POST['search_query']);
    $stmt = $connection->prepare("SELECT * FROM passwords WHERE website LIKE ?");
    $searchTerm = "%$searchQuery%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $searchResults[] = $row;
    }
    $stmt->close();
}

// Fetch categories
$categories = [];
$result = $connection->query("SELECT * FROM categories");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Password Vault</title>
     <!-- Add Font Awesome for eye icon -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding: 20px 0;
        }

        .header h1 {
            margin: 0;
            font-size: 2em;
        }

        .content {
            margin-top: 20px;
        }

        .logout-button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }

        .logout-button:hover {
            background-color: #0056b3;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        .form-group button {
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #28a745;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-group button:hover {
            background-color: #218838;
        }

        .search-results {
            margin-top: 20px;
        }

        .search-results table {
            width: 100%;
            border-collapse: collapse;
        }

        .search-results th, .search-results td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        .search-results th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Your Password Vault</h1>
        </div>
        <div class="content">
            <h2>Generate Password</h2>
            <form action="dashboard.php" method="post">
                <div class="form-group">
                    <label for="password_length">Password Length:</label>
                    <input type="number" id="password_length" name="password_length" value="12" min="4" max="64" required>
                </div>
                <div class="form-group">
                    <label for="include_numbers">
                        <input type="checkbox" id="include_numbers" name="include_numbers" checked> Include Numbers
                    </label>
                </div>
                <div class="form-group">
                    <label for="include_uppercase">
                        <input type="checkbox" id="include_uppercase" name="include_uppercase" checked> Include Uppercase Letters
                    </label>
                </div>
                <div class="form-group">
                    <label for="include_lowercase">
                        <input type="checkbox" id="include_lowercase" name="include_lowercase" checked> Include Lowercase Letters
                    </label>
                </div>
                <div class="form-group">
                    <label for="include_special_chars">
                        <input type="checkbox" id="include_special_chars" name="include_special_chars" checked> Include Special Characters
                    </label>
                </div>
                <div class="form-group">
                    <button type="submit" name="generate_password">Generate Password</button>
                </div>
            </form>

            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_password'])) {
                $length = $_POST['password_length'];
                $includeNumbers = isset($_POST['include_numbers']);
                $includeUppercase = isset($_POST['include_uppercase']);
                $includeLowercase = isset($_POST['include_lowercase']);
                $includeSpecialChars = isset($_POST['include_special_chars']);
                $generatedPassword = generateRandomPassword($length, $includeNumbers, $includeUppercase, $includeLowercase, $includeSpecialChars);
                echo "<div class='form-group'><label for='generated_password'>Generated Password:</label><input type='text' id='generated_password' name='generated_password' value='$generatedPassword' readonly></div>";
            }
            ?>

            <h2>Store Password</h2>
            <form action="dashboard.php" method="post">
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
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <div style="position: relative;">
                        <input type="text" id="password" name="password" required style="padding-right: 30px;">
                        <i class="fas fa-eye toggle-password" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                    </div>
                    
                </div>
                <div class="form-group">
                    <button type="submit" name="store_password" onclick="alert('Your Password has been stored')">Store Password</button>
                </div>
            </form>

            <h2>Search Password</h2>
            <form action="dashboard.php" method="post">
                <div class="form-group">
                    <label for="search_query">Search by Website:</label>
                    <input type="text" id="search_query" name="search_query" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="search_password">Search</button>
                </div>
            </form>

            <form action="logout.php" method="post">
                <button type="submit" class="logout-button">Logout</button>
            </form>

            <?php if (!empty($searchResults)): ?>
                <div class="search-results">
                    <h2>Search Results</h2>
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
                            <?php foreach ($searchResults as $result): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($result['category_id']); ?></td>
                                    <td><?php echo htmlspecialchars($result['website']); ?></td>
                                    <td><?php echo htmlspecialchars($result['username']); ?></td>
                                    <td><?php echo htmlspecialchars($result['password']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- JavaScript to toggle password visibility -->
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordField = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            // Toggle the type attribute
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);

            // Toggle the icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>




