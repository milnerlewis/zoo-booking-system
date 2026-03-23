<?php
/**
 * Contact page form processing.
 */

session_start();
require_once 'db_config.php';

$message = '';
$messageType = '';

// form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    // data
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $messageText = trim($_POST['message'] ?? '');
    
    // validation
    $errors = [];
    
    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email address is required';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($messageText)) {
        $errors[] = 'Message is required';
    }

    if (empty($errors)) {
        // insert
        $sql = "INSERT INTO contact_messages (first_name, last_name, email, phone, subject, message, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('ssssss', $firstName, $lastName, $email, $phone, $subject, $messageText);
            
            if ($stmt->execute()) {
                $message = 'Thank you for your message! We will get back to you within 2 business days.';
                $messageType = 'success';
                
                // clear
                $_POST = [];
            } else {
                $message = 'Sorry, there was an error sending your message. Please try again.';
                $messageType = 'error';
            }
            
            $stmt->close();
        } else {
            $message = 'Database error. Please try again later.';
            $messageType = 'error';
        }
    } else {
        $message = implode('<br>', $errors);
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
    <title>Contact Us - Riget Zoo Adventures</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=3">
    <script src="js/main.js" defer></script>
    <style>
        body {
            background-image: url('images/contactimg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .contact-container {
            width: 90%;
            max-width: 800px;
            margin: 120px auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.15);
        }

        .contact-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #111;
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #c7d2b9;
        }

        .info-card h4 {
            margin: 0 0 10px 0;
            color: #111;
        }

        .info-card p {
            margin: 5px 0;
            color: #555;
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

        .form-group input,
        .form-group textarea {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .form-row {
            display: flex;
            gap: 25px;
            align-items: flex-start;
        }

        .form-row .form-group {
            flex: 1;
            min-width: 0;
            max-width: calc(50% - 12.5px);
        }

        .btn-send {
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

        .btn-send:hover {
            background: #b8c3aa;
        }

        .char-counter {
            text-align: right;
            font-size: 0.875rem;
            color: #666;
            margin-top: 5px;
        }

        .form-message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .form-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .form-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 600px) {
            .form-row {
                flex-direction: column;
            }
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
        <div class="contact-container">
            <h2>Get in Touch</h2>
            
            <?php if ($message): ?>
                <div class="form-message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="contact-info">
                <div class="info-card">
                    <h4>Visit Us</h4>
                    <p>Riget Zoo Adventures<br>
                    123 Wildlife Park Road<br>
                    Adventure City, AC1 2ZB</p>
                </div>
                <div class="info-card">
                    <h4>Call Us</h4>
                    <p>General Enquiries: 01234 567890<br>
                    Bookings: 01234 567891<br>
                    Emergency: 01234 567892</p>
                </div>
                <div class="info-card">
                    <h4>Opening Hours</h4>
                    <p>Mon-Sun: 9:00 AM - 5:00 PM<br>
                    Last entry: 4:00 PM<br>
                    Closed: Christmas Day</p>
                </div>
            </div>
            
            <form action="" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name: <span style="color:red">*</span></label>
                        <input type="text" id="first_name" name="first_name" required maxlength="50"
                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name: <span style="color:red">*</span></label>
                        <input type="text" id="last_name" name="last_name" required maxlength="50"
                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address: <span style="color:red">*</span></label>
                    <input type="email" id="email" name="email" required maxlength="100"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number (optional):</label>
                    <input type="tel" id="phone" name="phone" maxlength="20"
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject: <span style="color:red">*</span></label>
                    <input type="text" id="subject" name="subject" required maxlength="200"
                           value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="message">Message: <span style="color:red">*</span></label>
                    <textarea id="message" name="message" required maxlength="1000" 
                             placeholder="Tell us how we can help you..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" name="send_message" class="btn-send">Send Message</button>
            </form>
        </div>
    </main>
</body>
</html>