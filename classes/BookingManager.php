<?php
/**
 * Booking management.
 * Handles bookings, calculations, and availability.
 */

require_once __DIR__ . '/../db_config.php';

class BookingManager {
    private $db;
    
    // pricing
    private const ADULT_PRICE = 15.00;
    private const CHILD_PRICE = 12.00;
    private const FAMILY_PASS_PRICE = 45.00; // 2 adults + 2 children
    private const MAX_VISITORS_PER_BOOKING = 20;
    private const MAX_DAILY_CAPACITY = 500;
    
    public function __construct() {
        $this->db = DatabaseConfig::getInstance();
    }
    
    /**
     * booking
     */
    public function createBooking($bookingData) {
        try {
            $this->db->beginTransaction();
            
            // validate
            $validation = $this->validateBookingData($bookingData);
            if (!$validation['valid']) {
                $this->db->rollback();
                return ['success' => false, 'message' => $validation['message']];
            }
            
            // availability
            $availability = $this->checkAvailability($bookingData['visit_date'], $bookingData['visit_time']);
            if (!$availability['available']) {
                $this->db->rollback();
                return ['success' => false, 'message' => $availability['message']];
            }
            
            // pricing
            $pricing = $this->calculatePricing($bookingData['adults'], $bookingData['children']);
            
            // reference
            $bookingReference = $this->generateBookingReference();
            
            // insert
            $sql = "INSERT INTO bookings (user_id, visitor_name, email, phone, visit_date, visit_time, 
                                       adults, children, special_requirements, total_cost, booking_reference, 
                                       status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', NOW())";
            
            $params = [
                $bookingData['user_id'] ?? null,
                $bookingData['visitor_name'],
                $bookingData['email'],
                $bookingData['phone'],
                $bookingData['visit_date'],
                $bookingData['visit_time'],
                $bookingData['adults'],
                $bookingData['children'],
                $bookingData['special_requirements'] ?? null,
                $pricing['total_cost'],
                $bookingReference
            ];
            
            $stmt = $this->db->query($sql, $params);
            $bookingId = $this->db->lastInsertId();
            
            // activity
            $this->createBookingActivity($bookingId, 'booking_created');
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Booking confirmed successfully',
                'booking_id' => $bookingId,
                'booking_reference' => $bookingReference,
                'total_cost' => $pricing['total_cost'],
                'breakdown' => $pricing['breakdown']
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Booking creation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Booking failed. Please try again.'];
        }
    }
    
    /**
     * bookings
     */
    public function getUserBookings($userId, $filters = []) {
        try {
            $sql = "SELECT b.*, ba.activity_type, ba.created_at as activity_time
                    FROM bookings b
                    LEFT JOIN booking_activities ba ON b.id = ba.booking_id
                    WHERE b.user_id = ?";
            
            $params = [$userId];
            
            // filters
            if (isset($filters['status'])) {
                $sql .= " AND b.status = ?";
                $params[] = $filters['status'];
            }
            
            if (isset($filters['date_from'])) {
                $sql .= " AND b.visit_date >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (isset($filters['date_to'])) {
                $sql .= " AND b.visit_date <= ?";
                $params[] = $filters['date_to'];
            }
            
            $sql .= " ORDER BY b.visit_date DESC, b.visit_time DESC";
            
            $stmt = $this->db->query($sql, $params);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Get user bookings error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * availability
     */
    public function checkAvailability($date, $time) {
        try {
            // past
            $visitDate = new DateTime($date);
            $today = new DateTime();
            
            if ($visitDate <= $today) {
                return ['available' => false, 'message' => 'Cannot book for past dates'];
            }
            
            // capacity
            $sql = "SELECT SUM(adults + children) as total_visitors 
                    FROM bookings 
                    WHERE visit_date = ? AND status IN ('confirmed', 'pending')";
            
            $stmt = $this->db->query($sql, [$date]);
            $result = $stmt->fetch();
            $currentVisitors = $result['total_visitors'] ?? 0;
            
            if ($currentVisitors >= self::MAX_DAILY_CAPACITY) {
                return ['available' => false, 'message' => 'This date is fully booked'];
            }
            
            // slot
            $hourlyCapacity = self::MAX_DAILY_CAPACITY * 0.1;
            
            $sql = "SELECT SUM(adults + children) as hourly_visitors 
                    FROM bookings 
                    WHERE visit_date = ? AND visit_time = ? AND status IN ('confirmed', 'pending')";
            
            $stmt = $this->db->query($sql, [$date, $time]);
            $result = $stmt->fetch();
            $hourlyVisitors = $result['hourly_visitors'] ?? 0;
            
            if ($hourlyVisitors >= $hourlyCapacity) {
                return ['available' => false, 'message' => 'This time slot is fully booked'];
            }
            
            // status
            $availabilityPercentage = ($currentVisitors / self::MAX_DAILY_CAPACITY) * 100;
            
            $status = 'green'; // Good availability
            if ($availabilityPercentage >= 70) {
                $status = 'amber'; // Moderate availability
            }
            if ($availabilityPercentage >= 90) {
                $status = 'red'; // Limited availability
            }
            
            return [
                'available' => true,
                'message' => 'Available',
                'capacity_status' => $status,
                'availability_percentage' => $availabilityPercentage,
                'remaining_spaces' => self::MAX_DAILY_CAPACITY - $currentVisitors
            ];
            
        } catch (Exception $e) {
            error_log("Availability check error: " . $e->getMessage());
            return ['available' => false, 'message' => 'Unable to check availability'];
        }
    }
    
    /**
     * pricing
     */
    public function calculatePricing($adults, $children) {
        $breakdown = [];
        $totalCost = 0;
        
        // family
        $familyPasses = 0;
        if ($adults >= 2 && $children >= 2) {
            $familyPasses = min(floor($adults / 2), floor($children / 2));
            $totalCost += $familyPasses * self::FAMILY_PASS_PRICE;
            $breakdown[] = [
                'item' => 'Family Pass (2 adults + 2 children)',
                'quantity' => $familyPasses,
                'unit_price' => self::FAMILY_PASS_PRICE,
                'subtotal' => $familyPasses * self::FAMILY_PASS_PRICE
            ];
        }
        
        // remaining
        $remainingAdults = $adults - ($familyPasses * 2);
        $remainingChildren = $children - ($familyPasses * 2);
        
        // adult
        if ($remainingAdults > 0) {
            $adultCost = $remainingAdults * self::ADULT_PRICE;
            $totalCost += $adultCost;
            $breakdown[] = [
                'item' => 'Adult',
                'quantity' => $remainingAdults,
                'unit_price' => self::ADULT_PRICE,
                'subtotal' => $adultCost
            ];
        }
        
        // child
        if ($remainingChildren > 0) {
            $childCost = $remainingChildren * self::CHILD_PRICE;
            $totalCost += $childCost;
            $breakdown[] = [
                'item' => 'Child (3-16)',
                'quantity' => $remainingChildren,
                'unit_price' => self::CHILD_PRICE,
                'subtotal' => $childCost
            ];
        }
        
        return [
            'total_cost' => round($totalCost, 2),
            'breakdown' => $breakdown,
            'family_passes_used' => $familyPasses
        ];
    }
    
    /**
     * statistics
     */
    public function getBookingStatistics($dateFrom = null, $dateTo = null) {
        try {
            $dateFrom = $dateFrom ?? date('Y-m-01'); // Start of current month
            $dateTo = $dateTo ?? date('Y-m-t'); // End of current month
            
            // daily
            $sql = "SELECT visit_date, 
                           SUM(adults + children) as total_visitors,
                           COUNT(*) as booking_count,
                           SUM(total_cost) as daily_revenue
                    FROM bookings 
                    WHERE visit_date BETWEEN ? AND ? AND status = 'confirmed'
                    GROUP BY visit_date
                    ORDER BY visit_date";
            
            $stmt = $this->db->query($sql, [$dateFrom, $dateTo]);
            $dailyStats = $stmt->fetchAll();
            
            // capacity
            foreach ($dailyStats as &$day) {
                $percentage = ($day['total_visitors'] / self::MAX_DAILY_CAPACITY) * 100;
                if ($percentage < 70) {
                    $day['status'] = 'green';
                } elseif ($percentage < 90) {
                    $day['status'] = 'amber';
                } else {
                    $day['status'] = 'red';
                }
                $day['capacity_percentage'] = round($percentage, 1);
            }
            
            return [
                'daily_stats' => $dailyStats,
                'summary' => $this->calculateSummaryStats($dailyStats)
            ];
            
        } catch (Exception $e) {
            error_log("Booking statistics error: " . $e->getMessage());
            return ['daily_stats' => [], 'summary' => []];
        }
    }
    
    /**
     * validate
     */
    private function validateBookingData($data) {
        // required
        $required = ['visitor_name', 'email', 'phone', 'visit_date', 'visit_time', 'adults'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['valid' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
            }
        }
        
        // visitors
        $adults = intval($data['adults']);
        $children = intval($data['children'] ?? 0);
        
        if ($adults < 1) {
            return ['valid' => false, 'message' => 'At least one adult is required'];
        }
        
        if ($adults + $children > self::MAX_VISITORS_PER_BOOKING) {
            return ['valid' => false, 'message' => 'Maximum ' . self::MAX_VISITORS_PER_BOOKING . ' visitors per booking'];
        }
        
        // email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Please enter a valid email address'];
        }
        
        // phone
        if (!preg_match('/^[\+]?[0-9\s\-\(\)]{10,15}$/', $data['phone'])) {
            return ['valid' => false, 'message' => 'Please enter a valid phone number'];
        }
        
        return ['valid' => true, 'message' => 'Valid booking data'];
    }
    
    /**
     * reference
     */
    private function generateBookingReference() {
        do {
            $reference = 'RZA' . date('y') . strtoupper(substr(md5(uniqid()), 0, 6));
            
            $sql = "SELECT COUNT(*) as count FROM bookings WHERE booking_reference = ?";
            $stmt = $this->db->query($sql, [$reference]);
            $exists = $stmt->fetch()['count'] > 0;
            
        } while ($exists);
        
        return $reference;
    }
    
    /**
     * activity
     */
    private function createBookingActivity($bookingId, $activityType) {
        $sql = "INSERT INTO booking_activities (booking_id, activity_type, created_at) VALUES (?, ?, NOW())";
        $this->db->query($sql, [$bookingId, $activityType]);
    }
    
    /**
     * summary
     */
    private function calculateSummaryStats($dailyStats) {
        if (empty($dailyStats)) {
            return [];
        }
        
        $totalVisitors = array_sum(array_column($dailyStats, 'total_visitors'));
        $totalRevenue = array_sum(array_column($dailyStats, 'daily_revenue'));
        $totalBookings = array_sum(array_column($dailyStats, 'booking_count'));
        
        return [
            'total_visitors' => $totalVisitors,
            'total_revenue' => round($totalRevenue, 2),
            'total_bookings' => $totalBookings,
            'average_daily_visitors' => round($totalVisitors / count($dailyStats), 1),
            'average_booking_value' => $totalBookings > 0 ? round($totalRevenue / $totalBookings, 2) : 0
        ];
    }
}
?>