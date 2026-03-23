<?php
session_start();
require_once 'db_config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Username already exists.';
        } else {
            $stmt = $conn->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $username, $email, $password);

            if ($stmt->execute()) {
                $success = "Account created successfully! You can now <a href='login.php'>log in</a>.";
            } else {
                $error = 'Error creating account. Please try again.';
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="icon" href="images/RigetZooAdv.png" type="image/png">
    <title>Sign Up - Riget Zoo Adventures</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=2">
    <style>
        body {
            background-image: url('images/signupimg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .signup-container {
            width: 90%;
            max-width: 450px;
            margin: 120px auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.15);
        }

        .signup-container h2 {
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

        .btn-signup {
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

        .btn-signup:hover {
            background: #b8c3aa;
        }

        .error {
            background: #ffebee;
            border: 1px solid #e57373;
            color: #c62828;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        .success {
            background: #e8f5e8;
            border: 1px solid #81c784;
            color: #2e7d32;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
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
        <div class="signup-container">
            <h2>Create your account</h2>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn-signup">Create Account</button>
            </form>
            
            <div class="form-footer">
                Already have an account? <a href="login.php">Log in here</a>
            </div>
        </div>
    </main>
</body>
</html>
