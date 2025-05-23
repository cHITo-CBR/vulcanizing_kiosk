<?php
session_start();
require_once 'config/database.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'kiosk';

    // Allow any password for admin role
    if ($role === 'admin') {
        $_SESSION['admin'] = $username ?: 'admin';
        header('Location: admin/dashboard.php');
        exit();
    } else {
        // For kiosk/client, just redirect to kiosk page
        header('Location: kiosk/index.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Vulcanizing Kiosk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background:rgb(48, 51, 63); 
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #18191d;
            border-radius: 1.2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.45);
            padding: 2.5rem 2rem 2rem 2rem;
            width: 370px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .login-logo {
            background: #23242a;
            border-radius: 50%;
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.2rem;
        }
        .login-logo i {
            color: #28a7ff;
            font-size: 2rem;
        }
        .login-title {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        .login-subtitle {
            color: #b0b3b8;
            font-size: 0.98rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .login-subtitle a {
            color: #28a7ff;
            text-decoration: none;
            font-weight: 500;
        }
        .login-form {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
        }
        .login-form input, .login-form select {
            background: #23242a;
            border: 1px solid #23242a;
            color: #e4e6eb;
            border-radius: 0.6rem;
            padding: 0.85rem 1rem;
            font-size: 1rem;
            outline: none;
            transition: border 0.2s;
        }
        .login-form input:focus, .login-form select:focus {
            border: 1.5px solid #28a7ff;
        }
        .login-form button {
            background: #28a7ff;
            color: #fff;
            border: none;
            border-radius: 0.6rem;
            padding: 0.85rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .login-form button:hover {
            background: #1e7fd6;
        }
        .login-or {
            color: #b0b3b8;
            text-align: center;
            margin: 1.2rem 0 0.7rem 0;
            font-size: 0.95rem;
            position: relative;
        }
        .login-or:before, .login-or:after {
            content: '';
            display: inline-block;
            width: 40px;
            height: 1px;
            background: #23242a;
            vertical-align: middle;
            margin: 0 10px;
        }
        .social-btns {
            display: flex;
            gap: 0.7rem;
            justify-content: center;
            margin-top: 0.2rem;
        }
        .social-btn {
            background: #23242a;
            border: none;
            border-radius: 0.5rem;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.4rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .social-btn:hover {
            background: #222b3a;
        }
        .error {
            color: #ff4d4f;
            background: #2a1a1a;
            border: 1px solid #ff4d4f33;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        @media (max-width: 500px) {
            .login-card { width: 98vw; padding: 2rem 0.5rem; }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <i class="fas fa-circle-notch"></i>
        </div>
        <div class="login-title">VulcaTech Kiosk</div>
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off" class="login-form">
            <input type="text" name="username" placeholder="email address" required autofocus>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" required>
              
                <option value="admin">Admin</option>
            </select>
            <button type="submit">Login</button>
        </form>
        
    </div>
</body>
</html> 