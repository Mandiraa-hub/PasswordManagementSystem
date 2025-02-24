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

// Function to check password strength and score it out of 5
function checkPasswordStrength($password) {
    $score = 0;
    $missing = [];

    // Check length
    if (strlen($password) >= 8) {
        $score++;
    } else {
        $missing[] = "at least 8 characters";
    }

    // Check for lowercase letters
    if (preg_match('/[a-z]/', $password)) {
        $score++;
    } else {
        $missing[] = "lowercase letters";
    }

    // Check for uppercase letters
    if (preg_match('/[A-Z]/', $password)) {
        $score++;
    } else {
        $missing[] = "uppercase letters";
    }

    // Check for numbers
    if (preg_match('/[0-9]/', $password)) {
        $score++;
    } else {
        $missing[] = "numbers";
    }

    // Check for special characters
    if (preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $score++;
    } else {
        $missing[] = "special characters";
    }

    return ['score' => $score, 'missing' => $missing];
}

// Calculate the number of passwords for each score
$scoreCounts = array_fill(0, 6, 0); // Initialize an array with 6 elements (0 to 5) all set to 0
foreach ($allPasswords as $passwordData) {
    $strength = checkPasswordStrength($passwordData['password']);
    $scoreCounts[$strength['score']]++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Strength Checker</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>

body {
    background: #000000; /* Black background */
    color: #2ecc71; /* Navy green text */
    font-family: 'Arial', sans-serif;
    height: 100vh;
    margin: 0;
    display: flex;
}

/* Content Wrapper - Increased Padding to Avoid Clutter */
.content {
    flex: 1;
    padding: 60px 50px 30px 300px; /* Adjusted padding: top, right, bottom, left */
    overflow-y: auto;
    box-sizing: border-box;
}

/* Strength Checker Card - Better Centering and More Space */
.container {
    background: #1a1a1a; /* Dark card background */
    border-radius: 10px;
    padding: 30px;
    width: 100%;
    max-width: 900px;
    margin: 50px auto; /* Add margin for better spacing */
}

/* Table Adjustments */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px; /* Adds space above the table */
}

th, td {
    padding: 15px;
    text-align: left;
    border: 1px solid #444;
}

th {
    background: #2ecc71;
    color: #000000;
    font-weight: bold;
}

tr:nth-child(even) {
    background: #2a2a2a;
}

tr:hover {
    background: #444;
}

/* Chart Centering */
.chart-container {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}
      
        .missing {
            color: #e74c3c; /* Bright red for missing criteria */
        }

        .chart {
            max-width: 600px;
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="container">
            <div class="chart-container">
                <canvas id="strengthChart" class="chart"></canvas>
            </div>
            <?php if (!empty($allPasswords)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Website</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Strength Score</th>
                        <th>Missing Criteria</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allPasswords as $passwordData): ?>
                        <?php $strength = checkPasswordStrength($passwordData['password']); ?>
                        <tr>
                            <td><?php echo htmlspecialchars($passwordData['name']); ?></td>
                            <td><?php echo htmlspecialchars($passwordData['website']); ?></td>
                            <td><?php echo htmlspecialchars($passwordData['username']); ?></td>
                            <td><?php echo htmlspecialchars($passwordData['password']); ?></td>
                            <td class="score"><?php echo $strength['score']; ?>/5</td>
                            <td class="missing"><?php echo implode(', ', $strength['missing']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="no-passwords">No passwords stored yet.</div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        // JavaScript to create the chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('strengthChart').getContext('2d');
            const strengthChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Score 0', 'Score 1', 'Score 2', 'Score 3', 'Score 4', 'Score 5'],
                    datasets: [{
                        label: 'Password Strength',
                        data: <?php echo json_encode($scoreCounts); ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed !== null) {
                                        label += context.parsed;
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>