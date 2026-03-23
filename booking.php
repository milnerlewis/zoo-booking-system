<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="icon" href="images/RigetZooAdv.png" type="image/png">
    <title>Choose Your Booking - Riget Zoo Adventures</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=3">
    <script src="js/main.js" defer></script>
    <style>
        body {
            background-image: url('images/bookingimg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .booking-selection-container {
            width: 90%;
            max-width: 800px;
            margin: 120px auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.15);
        }

        .booking-selection-container h2 {
            text-align: center;
            margin-bottom: 40px;
            color: #111;
            font-size: 2.5em;
        }

        .booking-options {
            display: flex;
            gap: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .booking-option {
            flex: 1;
            min-width: 300px;
            max-width: 350px;
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .booking-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .booking-option h3 {
            color: #2e7d32;
            margin-bottom: 20px;
            font-size: 1.8em;
        }

        .booking-option p {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .booking-option .features {
            list-style: none;
            padding: 0;
            margin: 20px 0;
            text-align: left;
        }

        .booking-option .features li {
            padding: 8px 0 8px 20px;
            color: #555;
            position: relative;
        }

        .booking-option .features li::before {
            content: '✓';
            color: #4caf50;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        .btn-book-type {
            display: inline-block;
            background: #c7d2b9;
            border: none;
            padding: 15px 30px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            color: #111;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.3s ease;
            width: 100%;
            box-sizing: border-box;
        }

        .btn-book-type:hover {
            background: #b8c3aa;
        }

        .zoo-option {
            border-top: 4px solid #4caf50;
        }

        .hotel-option {
            border-top: 4px solid #2196f3;
        }

        @media (max-width: 768px) {
            .booking-options {
                flex-direction: column;
            }

            .booking-option {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <header class="nav-wrap">
        <nav class="navbar" id="navigation">
            <div class="logo" aria-hidden="true"></div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#about-section">About us</a></li>
                <li><a href="booking.php">Book</a></li>
                <li><a href="contact.php">Contact us</a></li>
            </ul>
            <div class="nav-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Log in</a>
                    <a href="signup.php">Sign up</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    
    <main id="main-content">
        <div class="booking-selection-container">
            <h2>What would you like to book?</h2>
            
            <div class="booking-options">
                <div class="booking-option zoo-option">
                    <h3>🦁 Zoo Day Visit</h3>
                    <p>Experience our amazing wildlife with a day ticket to Riget Zoo Adventures!</p>
                    
                    <ul class="features">
                        <li>Access to all animal exhibits</li>
                        <li>Educational shows and presentations</li>
                        <li>Family-friendly activities</li>
                        <li>Playground and picnic areas</li>
                        <li>Gift shop access</li>
                        <li>Loyalty points on bookings</li>
                    </ul>
                    
                    <p><strong>From £12.00 per person</strong></p>
                    <p><em>Family discounts available!</em></p>
                    
                    <a href="zoobooking.php" class="btn-book-type">Book Zoo Visit</a>
                </div>
                
                <div class="booking-option hotel-option">
                    <h3>🏨 Hotel Stay</h3>
                    <p>Stay overnight and wake up to the sounds of nature at our on-site accommodation!</p>
                    
                    <ul class="features">
                        <li>Comfortable themed rooms</li>
                        <li>Complimentary zoo admission</li>
                        <li>Restaurant and room service</li>
                        <li>Early morning animal feeding</li>
                        <li>Evening wildlife talks</li>
                        <li>Free Wi-Fi and parking</li>
                    </ul>
                    
                    <p><strong>From £89.00 per night</strong></p>
                    <p><em>Zoo admission included!</em></p>
                    
                    <a href="hotelbooking.php" class="btn-book-type">Book Hotel Stay</a>
                </div>
            </div>
        </div>
    </main>

</body>
</html>

