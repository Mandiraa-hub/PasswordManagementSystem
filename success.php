<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Success</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1A202C; /* Dark Blue background */
            color: #E2E8F0; /* Light Gray text */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .card {
            background-color: #2D3748; /* Slightly lighter Dark Blue */
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 300px;
            text-align: center;
        }

        .card h2 {
            margin-top: 0;
            color: #32CD32; /* Neon Green */
        }

        .card p {
            color: #E2E8F0; /* Light Gray text */
            font-size: 14px;
            line-height: 1.5;
        }

        .card button {
            background-color: #32CD32; /* Neon Green */
            color: #1A202C; /* Dark text */
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .card button:hover {
            background-color: #28A745; /* Darker green on hover */
        }

        .card a {
            color: #1A202C; /* Dark text */
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Thank you for registering</h2>
        <p>You may now proceed with login</p>
        <button><a href="login.php">Login</a></button>
    </div>
</body>
</html>