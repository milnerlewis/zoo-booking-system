<?php
session_start();
require_once 'functions.php';
require_once 'db_config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Get user bookings
$userBookings = getUserBookings($_SESSION['user_id'], $conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="icon" href="images/RigetZooAdv.png" type="image/png">
    <title>My Account - Riget Zoo Adventures</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=2">
    <style>
        body{background-image:url('images/manageaccimg.jpeg');background-size:cover;background-position:center;background-attachment:fixed}
        .dashboard-container{width:90%;max-width:800px;margin:120px auto;background:rgba(255,255,255,0.95);border-radius:12px;padding:40px;box-shadow:0 6px 24px rgba(0,0,0,0.15)}
        .dashboard-container h2{text-align:center;margin-bottom:30px;color:#111}
        .welcome-message{background:#e8f5e8;border:1px solid #81c784;color:#2e7d32;padding:15px;border-radius:6px;margin-bottom:30px;text-align:center}
        .account-info{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:30px}
        .info-card{background:#f8f9fa;padding:20px;border-radius:8px;border-left:4px solid #c7d2b9}
        .info-card h4{margin:0 0 15px 0;color:#111}
        .info-card p{margin:5px 0;color:#555}
        .quick-actions{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;margin-top:20px}
        .action-btn{display:block;background:#c7d2b9;color:#111;text-decoration:none;padding:15px;border-radius:8px;text-align:center;font-weight:600;transition:background 0.3s}
        .action-btn:hover{background:#b8c3aa}
        .logout-btn{background:#dc3545;color:white}
        .logout-btn:hover{background:#c82333}
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
                <a href="dashboard.php">My Account</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>
    </header>
    
    <main>
        <div class="dashboard-container">
            <h2>My Account</h2>
            
            <div class="welcome-message">
                <strong>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</strong><br>
                Your account is active and ready for your next zoo adventure.
            </div>
            
            <div class="account-info">
                <div class="info-card">
                    <h4>Account Details</h4>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                    <p><strong>Account ID:</strong> #<?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
                </div>
                <div class="info-card">
                    <h4>Loyalty Points</h4>
                    <p><strong>Current Points:</strong> <?php echo $_SESSION['loyalty_points'] ?? 0; ?></p>
                        <?php if (($_SESSION['loyalty_points'] ?? 0) >= 10): ?>
                            <p style="color: #28a745;"><strong>10% discount available!</strong></p>
                    <?php else: ?>
                        <p>Earn <?php echo 10 - ($_SESSION['loyalty_points'] ?? 0); ?> more points for a 10% discount</p>
                    <?php endif; ?>
                    <small>1 point per ticket, 5 points per family ticket</small>
                </div>
                <div class="info-card">
                    <h4>Booking Summary</h4>
                    <p><strong>Total Bookings:</strong> <?php echo count($userBookings); ?></p>
                    <?php if (!empty($userBookings)): ?>
                        <p><strong>Next Visit:</strong> <?php echo date('d M Y', strtotime($userBookings[0]['visit_date'])); ?></p>
                        <p><strong>Total Spent:</strong> £<?php echo number_format(array_sum(array_column($userBookings, 'total_cost')), 2); ?></p>
                    <?php else: ?>
                        <p>No bookings yet</p>
                        <p>Ready for your first visit!</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($userBookings)): ?>
                <div style="margin-top: 30px;">
                    <h3>Your Bookings</h3>
                    <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <?php foreach($userBookings as $booking): ?>
                            <div style="padding: 15px; border-bottom: 1px solid #eee;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong><?php echo htmlspecialchars($booking['booking_reference']); ?></strong> - 
                                        <?php echo htmlspecialchars($booking['visitor_name']); ?>
                                        <br>
                                        <small>
                                            <?php echo date('d M Y', strtotime($booking['visit_date'])); ?> at 
                                            <?php echo date('g:i A', strtotime($booking['visit_time'])); ?>
                                        </small>
                                    </div>
                                    <div style="text-align: right;">
                                        <span style="color: #2d5016; font-weight: 600;">£<?php echo number_format($booking['total_cost'], 2); ?></span>
                                        <?php if ($booking['discount_applied'] > 0): ?>
                                            <br><small style="color: #28a745;">Discount: -<?php echo $booking['discount_applied']; ?>%</small>
                                        <?php endif; ?>
                                        <br>
                                        <small>
                                            <?php echo $booking['adults']; ?> adults, <?php echo $booking['children']; ?> children
                                            <?php if ($booking['family_tickets'] > 0): ?>
                                                , <?php echo $booking['family_tickets']; ?> family tickets
                                            <?php endif; ?>
                                        </small>
                                        <?php if ($booking['loyalty_points_earned'] > 0): ?>
                                            <br><small style="color: #007bff;">+<?php echo $booking['loyalty_points_earned']; ?> points earned</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="quick-actions">
                <a href="booking.php" class="action-btn">Book a Visit</a>
                <a href="contact.php" class="action-btn">Contact Us</a>
                <a href="index.php" class="action-btn">Explore Animals</a>
                <a href="logout.php" class="action-btn logout-btn">Logout</a>
            </div>
        </div>
    </main>

</body>
</html>
