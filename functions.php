<?php
// Core application helper functions.

require_once 'db_config.php';

// Register user.
function registerUser($username, $email, $password, $conn) {
    // validation
    if (empty($username) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    if (strlen($username) < 3) {
        return ['success' => false, 'message' => 'Username must be at least 3 characters'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address'];
    }
    
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters'];
    }
    
    // exists
    $checkSql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('ss', $username, $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Username or email already exists'];
    }
    
    // insert
    $sql = "INSERT INTO users (username, email, password, loyalty_points, total_points_earned, created_at) VALUES (?, ?, ?, 0, 0, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $username, $email, $password);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Registration successful! You can now log in.'];
    } else {
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

// Log in user.
function loginUser($username, $password, $conn) {
    if (empty($username) || empty($password)) {
        return ['success' => false, 'message' => 'Username and password are required'];
    }
    
    $sql = "SELECT id, username, email, password, loyalty_points FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['success' => false, 'message' => 'Invalid username or password'];
    }
    
    $user = $result->fetch_assoc();
    
    if ($password !== $user['password']) {
        return ['success' => false, 'message' => 'Invalid username or password'];
    }
    
    // session
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['loyalty_points'] = $user['loyalty_points'];
    
    return ['success' => true, 'message' => 'Login successful!'];
}

// Create booking.
function createBooking($bookingData, $conn) {
    // extract
    $visitorName = $bookingData['visitor_name'];
    $email = $bookingData['email'];
    $phone = $bookingData['phone'];
    $visitDate = $bookingData['visit_date'];
    $visitTime = $bookingData['visit_time'];
    $adults = intval($bookingData['adults']);
    $children = intval($bookingData['children']);
    $familyTickets = intval($bookingData['family_tickets'] ?? 0);
    $specialReq = $bookingData['special_requirements'] ?? '';
    $userId = $bookingData['user_id'] ?? null;
    $useDiscount = isset($bookingData['use_loyalty_discount']) && $bookingData['use_loyalty_discount'] === 'yes';
    
    // validate
    if (empty($visitorName) || empty($email) || empty($phone) || empty($visitDate) || empty($visitTime)) {
        return ['success' => false, 'message' => 'All required fields must be filled'];
    }
    
    if ($adults < 1 && $familyTickets < 1) {
        return ['success' => false, 'message' => 'At least one adult or one family ticket is required'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address'];
    }
    
    // email
    if (!$userId) {
        $checkUserSql = "SELECT id FROM users WHERE email = ?";
        $checkStmt = $conn->prepare($checkUserSql);
        $checkStmt->bind_param('s', $email);
        $checkStmt->execute();
        $userResult = $checkStmt->get_result();
        if ($userResult->num_rows > 0) {
            $userRow = $userResult->fetch_assoc();
            $userId = $userRow['id'];
        }
    }
    
    // calculate
    $originalCost = calculateBookingPrice($adults, $children, $familyTickets);
    $loyaltyPoints = calculateLoyaltyPoints($adults, $children, $familyTickets);
    $discountPercent = 0;
    $finalCost = $originalCost;
    
    // discount
    if ($useDiscount && $userId) {
        $userPoints = getUserLoyaltyPoints($userId, $conn);
        if ($userPoints >= 10) {
            $discountPercent = 10;
            $finalCost = $originalCost * 0.9; // 10% discount
        }
    }
    
    // reference
    $bookingRef = 'RZA' . date('y') . strtoupper(substr(md5(uniqid()), 0, 6));
    
    // insert
    $sql = "INSERT INTO bookings (user_id, visitor_name, email, phone, visit_date, visit_time, 
                                 adults, children, family_tickets, special_requirements, total_cost, 
                                 original_cost, discount_applied, loyalty_points_earned, booking_reference, 
                                 status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isssssiiisddiis', $userId, $visitorName, $email, $phone, $visitDate, $visitTime,
                     $adults, $children, $familyTickets, $specialReq, $finalCost, 
                     $originalCost, $discountPercent, $loyaltyPoints, $bookingRef);
    
    if ($stmt->execute()) {
        // points
        if ($userId) {
            updateUserLoyaltyPoints($userId, $loyaltyPoints, $useDiscount ? 10 : 0, $conn);
        }
        
        return [
            'success' => true, 
            'message' => 'Booking confirmed!', 
            'booking_reference' => $bookingRef,
            'total_cost' => $finalCost,
            'original_cost' => $originalCost,
            'discount_applied' => $discountPercent,
            'loyalty_points_earned' => $loyaltyPoints
        ];
    } else {
        return ['success' => false, 'message' => 'Booking failed. Please try again.'];
    }
}

// Calculate booking price.
function calculateBookingPrice($adults, $children, $familyTickets = 0) {
    $adultPrice = 15.00;
    $childPrice = 12.00;
    $familyTicketPrice = 45.00; // 2 adults + 2 children
    
    // family
    $familyCost = $familyTickets * $familyTicketPrice;
    
    // remaining
    $remainingAdults = max(0, $adults - ($familyTickets * 2));
    $remainingChildren = max(0, $children - ($familyTickets * 2));
    
    $individualCost = ($remainingAdults * $adultPrice) + ($remainingChildren * $childPrice);
    
    return round($familyCost + $individualCost, 2);
}

// Calculate loyalty points.
function calculateLoyaltyPoints($adults, $children, $familyTickets = 0) {
    $points = 0;
    
    // adult
    $points += $adults;
    
    // child
    $points += $children;
    
    // family
    $points += $familyTickets * 5;
    
    return $points;
}

// Get user loyalty points.
function getUserLoyaltyPoints($userId, $conn) {
    $sql = "SELECT loyalty_points FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return intval($row['loyalty_points']);
    }
    
    return 0;
}

// Update user loyalty points.
function updateUserLoyaltyPoints($userId, $pointsEarned, $pointsRedeemed, $conn) {
    $sql = "UPDATE users SET 
            loyalty_points = loyalty_points + ? - ?,
            total_points_earned = total_points_earned + ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiii', $pointsEarned, $pointsRedeemed, $pointsEarned, $userId);
    $stmt->execute();
}

// Optimize ticket selection.
function optimizeTicketSelection($adults, $children) {
    $adultPrice = 15.00;
    $childPrice = 12.00;
    $familyTicketPrice = 45.00;
    
    // best
    $maxFamilyTickets = min(floor($adults / 2), floor($children / 2));
    $bestCombination = ['adults' => $adults, 'children' => $children, 'family_tickets' => 0, 'cost' => ($adults * $adultPrice) + ($children * $childPrice)];
    
    for ($familyTickets = 1; $familyTickets <= $maxFamilyTickets; $familyTickets++) {
        $remainingAdults = $adults - ($familyTickets * 2);
        $remainingChildren = $children - ($familyTickets * 2);
        $totalCost = ($familyTickets * $familyTicketPrice) + ($remainingAdults * $adultPrice) + ($remainingChildren * $childPrice);
        
        if ($totalCost < $bestCombination['cost']) {
            $bestCombination = [
                'adults' => $remainingAdults,
                'children' => $remainingChildren, 
                'family_tickets' => $familyTickets,
                'cost' => $totalCost
            ];
        }
    }
    
    return $bestCombination;
}

// Save contact message.
function saveContactMessage($messageData, $conn) {
    $firstName = $messageData['first_name'];
    $lastName = $messageData['last_name'];
    $email = $messageData['email'];
    $phone = $messageData['phone'] ?? '';
    $subject = $messageData['subject'];
    $message = $messageData['message'];
    
    $sql = "INSERT INTO contact_messages (first_name, last_name, email, phone, subject, message, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssss', $firstName, $lastName, $email, $phone, $subject, $message);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Message sent successfully!'];
    } else {
        return ['success' => false, 'message' => 'Failed to send message. Please try again.'];
    }
}

// Get all bookings for a user.
function getUserBookings($userId, $conn) {
    $sql = "SELECT booking_reference, visitor_name, visit_date, visit_time, adults, children, 
                   family_tickets, total_cost, original_cost, discount_applied, 
                   loyalty_points_earned, status, created_at 
            FROM bookings 
            WHERE user_id = ? 
            ORDER BY visit_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    return $bookings;
}

// Check daily booking availability.
function checkAvailability($date, $conn) {
    $maxDailyCapacity = 500;
    
    $sql = "SELECT SUM(adults + children) as total_visitors 
            FROM bookings 
            WHERE visit_date = ? AND status = 'confirmed'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $currentVisitors = $row['total_visitors'] ?? 0;
    $availabilityPercent = ($currentVisitors / $maxDailyCapacity) * 100;
    
    $status = 'green'; // Good availability
    if ($availabilityPercent >= 70) {
        $status = 'amber'; // Moderate
    }
    if ($availabilityPercent >= 90) {
        $status = 'red'; // Limited
    }
    
    return [
        'available' => $currentVisitors < $maxDailyCapacity,
        'status' => $status,
        'current_visitors' => $currentVisitors,
        'capacity_percent' => round($availabilityPercent, 1)
    ];
}

// Return whether a user is logged in.
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Log out current user.
function logoutUser() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
?>