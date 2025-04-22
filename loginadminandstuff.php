<?php
session_start();

// Handle login form submission
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hardcoded admin credentials (replace with database check in production)
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #2c3e50, #34495e);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Login Form */
        .login-form {
            background: linear-gradient(145deg, #4a5568, #2d3748);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            width: 350px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center; /* Center align text */
        }

        .login-form h2 {
            margin-bottom: 20px;
            color: #fff;
            font-size: 24px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .login-form input {
            width: calc(100% - 24px); /* Adjust width to account for padding */
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .login-form input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .login-form input:focus {
            border-color: #48bb78;
        }

        .login-form button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(145deg, #48bb78, #38a169);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            transition: background 0.3s ease;
        }

        .login-form button:hover {
            background: linear-gradient(145deg, #38a169, #48bb78);
        }

        .error {
            color: #ff6b6b;
            margin-bottom: 15px;
            font-size: 14px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="login-form">
        <h2>Admin Login</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>