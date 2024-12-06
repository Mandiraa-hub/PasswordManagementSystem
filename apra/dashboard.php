<!-- In this code, the encryptPassword and decryptPassword functions are used to encrypt and decrypt the passwords using the master password. The master password is assumed to be stored in the session ($_SESSION['master_password). Make sure to securely handle the master password and session management. -->
<?php
// session_start();  // Start the session

// // Check if the user is logged in by verifying session variables
// if (!isset($_SESSION['user_id'])) {
//     // If the user is not logged in, redirect to the login page
//     header('Location: login.php');
//     exit();
// }


// Database connection
$connection = new mysqli('localhost', 'root', '', 'pms');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Encryption and decryption functions
function encryptPassword($password, $masterPassword) {
    $key = hash('sha256', $masterPassword);  // Create a 256-bit key
    $iv = openssl_random_pseudo_bytes(16);  // Generate a 16-byte IV
    $encryptedPassword = openssl_encrypt($password, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encryptedPassword);  // Store the IV with the encrypted password
}

function decryptPassword($encryptedPassword, $masterPassword) {
    $key = hash('sha256', $masterPassword);  // Create a 256-bit key
    $data = base64_decode($encryptedPassword);
    $iv = substr($data, 0, 16);  // Extract the IV
    $encryptedPassword = substr($data, 16);
    return openssl_decrypt($encryptedPassword, 'aes-256-cbc', $key, 0, $iv);
}

// Function to check password strength
function checkPasswordStrength($password) {
    if (strlen($password) > 15 &&
        preg_match('/[0-9]/', $password) &&
        preg_match('/[A-Z]/', $password) &&
        preg_match('/[a-z]/', $password) &&
        preg_match('/[!@#$%^&*()]/', $password)) {
        return 'Strong'; // Only mark strong if all conditions are met
    } elseif (strlen($password) >= 8 &&
              (preg_match('/[0-9]/', $password) ||
               preg_match('/[A-Z]/', $password) ||
               preg_match('/[!@#$%^&*()]/', $password))) {
        return 'Medium'; // Medium if some but not all conditions are met
    }
    return 'Weak'; // Mark as weak otherwise
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

    $stmt = $connection->prepare("INSERT INTO password (category_id, website, username, password, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $category_id, $website, $username, $password, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

// // Handle password search
// $searchResults = [];
// if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_password'])) {
//     $searchQuery = htmlspecialchars($_POST['search_query']);
//     $stmt = $connection->prepare("SELECT * FROM passwords WHERE website LIKE ?");
//     $searchTerm = "%$searchQuery%";
//     $stmt->bind_param("s", $searchTerm);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     while ($row = $result->fetch_assoc()) {
//         $searchResults[] = $row;
//     }
//     $stmt->close();
// }

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
    <title>Password Vault Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* General Styles */
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background:linear-gradient(145deg, #000428, #004e92);
            color: #f1f5f9;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 300px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
            transition: width 0.3s;
            overflow: hidden;
        }

        .sidebar:hover {
            width: 350px;
        }

        .sidebar h2 {
            color: #38bdf8;
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.8em;
        }

        .sidebar a {
            display: block;
            padding: 15px;
            margin: 10px 0;
            font-size: 1.2em;
            text-decoration: none;
            color: #f1f5f9;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.15);
            transition: all 0.3s ease;
        }

        .sidebar a:hover {
            background: rgba(56, 189, 248, 0.3);
            color: #ffffff;
            transform: translateX(10px);
        }

        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 20px;
            margin: 30px auto;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            color: #f1f5f9;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #f1f5f9;
        }

        .form-group input,
        .form-group button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 8px;
            color: #f1f5f9;
            background: rgba(255, 255, 255, 0.1);
        }

        .form-group button {
            background-color: #2563eb;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-group button:hover {
            background-color: #1d4ed8;
        }

        .message {
            padding: 15px;
            margin-top: 20px;
            background: rgba(56, 189, 248, 0.15);
            backdrop-filter: blur(5px);
            color: #ffffff;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Password Vault</h2>
        <a href="#generate-password"><i class="fas fa-key"></i> Generate Password</a>
        <a href="#store-password"><i class="fas fa-save"></i> Store Password</a>
        <a href="#view-passwords"><i class="fas fa-eye"></i> View Saved Passwords</a>
        <a href="#logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <div class="content">
        <?php if (!empty($generated_password)): ?>
            <div class="message">
                <strong>Generated Password:</strong> <?php echo htmlspecialchars($generated_password); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($stored_message)): ?>
            <div class="message"><?php echo htmlspecialchars($stored_message); ?></div>
        <?php endif; ?>

        <div class="glass-card" id="generate-password">
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
        </div>
        <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_password'])) {
                $length = $_POST['password_length'];
                $includeNumbers = isset($_POST['include_numbers']);
                $includeUppercase = isset($_POST['include_uppercase']);
                $includeLowercase = isset($_POST['include_lowercase']);
                $includeSpecialChars = isset($_POST['include_special_chars']);
                $generatedPassword = generateRandomPassword($length, $includeNumbers, $includeUppercase, $includeLowercase, $includeSpecialChars);
                $strengthText = checkPasswordStrength($generatedPassword);
            
                echo "<div class='form-group'>
                        <label for='generated_password'>Generated Password:</label>
                        <input type='text' id='generated_password' name='generated_password' value='$generatedPassword' readonly>
                      </div>";
                echo "<div class='form-group'>
                        <label>Password Strength:</label>
                        <input type='text' value='$strengthText' readonly>
                      </div>";
            }
            
            ?>

        <div class="glass-card" id="store-password">
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
                    <label for="username">Username or Email :</label>
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
        </div>
    </div>
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
