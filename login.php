<?php
session_start();
require_once 'functions.php';
require_once 'db_config.php';

$message = '';
$messageType = '';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = loginUser($username, $password, $conn);

    if ($result['success']) {
        header('Location: dashboard.php');
        exit();
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="icon" href="images/RigetZooAdv.png" type="image/png">
    <title>Login - Riget Zoo Adventures</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=2">
    <style>
        body {
            background-image: url('images/loginimg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .login-container {
            width: 90%;
            max-width: 400px;
            margin: 120px auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.15);
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #111;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #111;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }

        .btn-login {
            width: 100%;
            background: #c7d2b9;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            color: #111;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #b8c3aa;
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
        }

        .form-footer a {
            color: #111;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header class="nav-wrap">
        <nav class="navbar">
            <div class="logo" aria-hidden="true"></div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#about-section">About us</a></li>
                <li><a href="booking.php">Book</a></li>
                <li><a href="contact.php">Contact us</a></li>
            </ul>
            <div class="nav-actions">
                <a href="login.php">Log in</a>
                <a href="signup.php">Sign up</a>
            </div>
        </nav>
    </header>
    
    <main>
        <div class="login-container">
            <h2>Log in to your account</h2>
            <?php if ($message): ?>
                <div class="form-message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" name="login" class="btn-login">Log in</button>
            </form>
            
            <div class="form-footer">
                Don't have an account? <a href="signup.php">Sign up here</a>
            </div>
        </div>
    </main>
</body>
</html>
