<?php
session_start();
require_once 'functions.php';
require_once 'db_config.php';

$message = '';
$messageType = '';

// form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_visit'])) {
    // data
    $bookingData = [
        'user_id' => $_SESSION['user_id'] ?? null,
        'visitor_name' => trim($_POST['visitor_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'visit_date' => $_POST['visit_date'] ?? '',
        'visit_time' => $_POST['visit_time'] ?? '',
        'adults' => intval($_POST['adults'] ?? 0),
        'children' => intval($_POST['children'] ?? 0),
        'family_tickets' => intval($_POST['family_tickets'] ?? 0),
        'special_requirements' => trim($_POST['special_requirements'] ?? ''),
        'use_loyalty_discount' => $_POST['use_loyalty_discount'] ?? 'no'
    ];
    
    // process
    $result = createBooking($bookingData, $conn);
    
    if ($result['success']) {
        $message = "Booking confirmed! Reference: " . $result['booking_reference'] . 
                  " | Total cost: £" . number_format($result['total_cost'], 2);
        if ($result['discount_applied'] > 0) {
            $message .= " (£" . number_format($result['original_cost'], 2) . " - " . 
                       $result['discount_applied'] . "% discount)";
        }
        if ($result['loyalty_points_earned'] > 0) {
            $message .= " | Points earned: " . $result['loyalty_points_earned'];
        }
        $messageType = 'success';
        
        // session
        if (isset($_SESSION['user_id'])) {
            $_SESSION['loyalty_points'] = getUserLoyaltyPoints($_SESSION['user_id'], $conn);
        }
        
        // clear
        $_POST = [];
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
}

// availability
$today = date('Y-m-d');
$availability = checkAvailability($today, $conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="icon" href="images/RigetZooAdv.png" type="image/png">
    <title>Book Zoo Visit - Riget Zoo Adventures</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=3">
    <script src="js/main.js" defer></script>
    <style>
        body{background-image:url('images/bookingimg.jpg');background-size:cover;background-position:center;background-attachment:fixed}
        .booking-container{width:90%;max-width:600px;margin:120px auto;background:rgba(255,255,255,0.95);border-radius:12px;padding:40px;box-shadow:0 6px 24px rgba(0,0,0,0.15)}
        .booking-container h2{text-align:center;margin-bottom:30px;color:#111}
        .back-link{display:inline-block;margin-bottom:20px;color:#4caf50;text-decoration:none;font-weight:600}
        .back-link:hover{text-decoration:underline}
        .form-group{margin-bottom:20px}
        .form-group label{display:block;margin-bottom:8px;color:#111;font-weight:600}
        .form-group input,.form-group select{width:100%;box-sizing:border-box;padding:12px;border:1px solid #ddd;border-radius:6px;font-size:16px}
        .form-row{display:flex;gap:15px}
        .form-row .form-group{flex:1;min-width:0}
        .btn-book{width:100%;background:#c7d2b9;border:none;padding:12px;border-radius:6px;font-size:16px;font-weight:600;color:#111;cursor:pointer;margin-top:10px}
        .btn-book:hover{background:#b8c3aa}
        .booking-info{background:#f0f8ff;border:1px solid #4caf50;color:#2e7d32;padding:15px;border-radius:6px;margin-bottom:20px}
        .booking-info h4{margin:0 0 10px 0;color:#2e7d32}
        .availability-indicator{padding:10px;border-radius:6px;margin:10px 0;font-weight:600}
        .availability-green{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
        .availability-amber{background:#fff3cd;color:#856404;border:1px solid #ffeaa7}
        .availability-red{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
        .loyalty-info{margin-top:15px;padding-top:15px;border-top:1px solid #4caf50}
        .discount-available{background:#4caf50;color:white;padding:4px 8px;border-radius:4px;font-size:0.9em;margin-left:10px}
        @media (max-width:600px){.form-row{flex-direction:column}}
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
        <div class="booking-container">
            <a href="booking.php" class="back-link">← Back to Booking Options</a>
            <h2>🦁 Book Your Zoo Adventure</h2>
            
            <?php if ($message): ?>
                <div class="global-message global-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="booking-info">
                <h4>Zoo Admission Prices:</h4>
                <p>Adults: £15.00 | Children (3-16): £12.00 | Under 3: Free<br>
                <strong>Family Ticket (2 adults + 2 children): £45.00 - Save £15!</strong></p>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="loyalty-info">
                        <strong>Your Loyalty Points: <?php echo $_SESSION['loyalty_points'] ?? 0; ?></strong>
                        <?php if (($_SESSION['loyalty_points'] ?? 0) >= 10): ?>
                            <span class="discount-available">10% discount available!</span>
                        <?php else: ?>
                            <small>(Earn <?php echo 10 - ($_SESSION['loyalty_points'] ?? 0); ?> more points for a 10% discount)</small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($availability['status'])): ?>
                    <div class="availability-indicator availability-<?php echo $availability['status']; ?>">
                        Current capacity status: 
                        <?php 
                        switch($availability['status']) {
                            case 'green': echo 'Good availability'; break;
                            case 'amber': echo 'Moderate availability'; break;
                            case 'red': echo 'Limited availability'; break;
                        }
                        ?>
                        (<?php echo $availability['capacity_percent']; ?>% capacity)
                    </div>
                <?php endif; ?>
            </div>
            
            <form action="" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="visit_date">Visit Date: <span style="color:red">*</span></label>
                        <input type="date" id="visit_date" name="visit_date" required 
                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                               value="<?php echo htmlspecialchars($_POST['visit_date'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="visit_time">Preferred Time: <span style="color:red">*</span></label>
                        <select id="visit_time" name="visit_time" required>
                            <option value="">Select time</option>
                            <option value="09:00" <?php echo ($_POST['visit_time'] ?? '') === '09:00' ? 'selected' : ''; ?>>9:00 AM</option>
                            <option value="10:00" <?php echo ($_POST['visit_time'] ?? '') === '10:00' ? 'selected' : ''; ?>>10:00 AM</option>
                            <option value="11:00" <?php echo ($_POST['visit_time'] ?? '') === '11:00' ? 'selected' : ''; ?>>11:00 AM</option>
                            <option value="12:00" <?php echo ($_POST['visit_time'] ?? '') === '12:00' ? 'selected' : ''; ?>>12:00 PM</option>
                            <option value="13:00" <?php echo ($_POST['visit_time'] ?? '') === '13:00' ? 'selected' : ''; ?>>1:00 PM</option>
                            <option value="14:00" <?php echo ($_POST['visit_time'] ?? '') === '14:00' ? 'selected' : ''; ?>>2:00 PM</option>
                            <option value="15:00" <?php echo ($_POST['visit_time'] ?? '') === '15:00' ? 'selected' : ''; ?>>3:00 PM</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="adults">Adults:</label>
                        <input type="number" id="adults" name="adults" min="0" max="10" 
                               value="<?php echo htmlspecialchars($_POST['adults'] ?? '1'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="children">Children (3-16):</label>
                        <input type="number" id="children" name="children" min="0" max="10" 
                               value="<?php echo htmlspecialchars($_POST['children'] ?? '0'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="family_tickets">Family Tickets:</label>
                        <input type="number" id="family_tickets" name="family_tickets" min="0" max="5" 
                               value="<?php echo htmlspecialchars($_POST['family_tickets'] ?? '0'); ?>">
                        <small>Each family ticket covers 2 adults + 2 children</small>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['user_id']) && ($_SESSION['loyalty_points'] ?? 0) >= 10): ?>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="use_loyalty_discount" name="use_loyalty_discount" value="yes"
                                   <?php echo ($_POST['use_loyalty_discount'] ?? '') === 'yes' ? 'checked' : ''; ?>>
                            Use loyalty discount (10% off - costs 10 points)
                        </label>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="visitor_name">Lead Visitor Name: <span style="color:red">*</span></label>
                    <input type="text" id="visitor_name" name="visitor_name" required maxlength="100"
                           value="<?php echo htmlspecialchars($_POST['visitor_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address: <span style="color:red">*</span></label>
                    <input type="email" id="email" name="email" required maxlength="100"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? $_SESSION['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number: <span style="color:red">*</span></label>
                    <input type="tel" id="phone" name="phone" required maxlength="20"
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                           placeholder="e.g. 01234 567890">
                </div>
                
                <div class="form-group">
                    <label for="special_requirements">Special Requirements (optional):</label>
                    <input type="text" id="special_requirements" name="special_requirements" maxlength="250"
                           value="<?php echo htmlspecialchars($_POST['special_requirements'] ?? ''); ?>"
                           placeholder="Wheelchair access, dietary requirements, etc.">
                </div>
                
                <button type="submit" name="book_visit" class="btn-book">Book Your Zoo Visit</button>
            </form>
        </div>
    </main>

</body>
</html>
