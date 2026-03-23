<?php
/**
 * User management.
 * Handles authentication, registration, and profiles.
 */

require_once __DIR__ . '/../db_config.php';

class UserManager {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getInstance();
    }
    
    /**
     * register
     */
    public function registerUser($username, $email, $password) {
        try {
            // validate
            $validation = $this->validateUserInput($username, $email, $password);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }
            
            // exists
            if ($this->userExists($username, $email)) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            // hash
            $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
            
            // insert
            $sql = "INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $this->db->query($sql, [$username, $email, $hashedPassword]);
            
            $userId = $this->db->lastInsertId();
            
            // profile
            $this->createUserProfile($userId);
            
            return ['success' => true, 'message' => 'User registered successfully', 'user_id' => $userId];
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }
    
    /**
     * authenticate
     */
    public function loginUser($username, $password) {
        try {
            $sql = "SELECT id, username, email, password, last_login FROM users WHERE username = ? OR email = ?";
            $stmt = $this->db->query($sql, [$username, $username]);
            
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
            
            $user = $stmt->fetch();
            
            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
            
            // login
            $this->updateLastLogin($user['id']);
            
            // session
            $this->createUserSession($user);
            
            return [
                'success' => true, 
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }
    
    /**
     * profile
     */
    public function getUserProfile($userId) {
        try {
            $sql = "SELECT u.*, up.*, 
                           COUNT(DISTINCT b.id) as total_bookings,
                           COUNT(DISTINCT cm.id) as total_messages,
                           MAX(b.created_at) as last_booking
                    FROM users u
                    LEFT JOIN user_profiles up ON u.id = up.user_id
                    LEFT JOIN bookings b ON u.id = b.user_id
                    LEFT JOIN contact_messages cm ON u.email = cm.email
                    WHERE u.id = ?
                    GROUP BY u.id";
                    
            $stmt = $this->db->query($sql, [$userId]);
            
            if ($stmt->rowCount() === 0) {
                return null;
            }
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Profile retrieval error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * validate
     */
    private function validateUserInput($username, $email, $password) {
        // username
        if (strlen($username) < 3 || strlen($username) > 50) {
            return ['valid' => false, 'message' => 'Username must be between 3 and 50 characters'];
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return ['valid' => false, 'message' => 'Username can only contain letters, numbers, and underscores'];
        }
        
        // email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Please enter a valid email address'];
        }
        
        if (strlen($email) > 100) {
            return ['valid' => false, 'message' => 'Email address is too long'];
        }
        
        // password
        if (strlen($password) < 8) {
            return ['valid' => false, 'message' => 'Password must be at least 8 characters long'];
        }
        
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number'];
        }
        
        return ['valid' => true, 'message' => 'Valid input'];
    }
    
    /**
     * exists
     */
    private function userExists($username, $email) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = ? OR email = ?";
        $stmt = $this->db->query($sql, [$username, $email]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * profile
     */
    private function createUserProfile($userId) {
        $sql = "INSERT INTO user_profiles (user_id, created_at) VALUES (?, NOW())";
        $this->db->query($sql, [$userId]);
    }
    
    /**
     * login
     */
    private function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $this->db->query($sql, [$userId]);
    }
    
    /**
     * session
     */
    private function createUserSession($user) {
        session_start();
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['login_time'] = time();
        
        // store
        $sessionId = session_id();
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $sql = "INSERT INTO user_sessions (id, user_id, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE last_activity = NOW(), ip_address = ?, user_agent = ?";
        
        $this->db->query($sql, [$sessionId, $user['id'], $ipAddress, $userAgent, $ipAddress, $userAgent]);
    }
    
    /**
     * logout
     */
    public function logoutUser() {
        session_start();
        
        if (isset($_SESSION['user_id'])) {
            // remove
            $sessionId = session_id();
            $sql = "DELETE FROM user_sessions WHERE id = ?";
            $this->db->query($sql, [$sessionId]);
        }
        
        // clear
        $_SESSION = array();
        
        // delete
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
    
    /**
     * valid
     */
    public function isValidSession() {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // timeout
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 86400) {
            $this->logoutUser();
            return false;
        }
        
        // verify
        $sessionId = session_id();
        $sql = "SELECT COUNT(*) as count FROM user_sessions WHERE id = ? AND user_id = ?";
        $stmt = $this->db->query($sql, [$sessionId, $_SESSION['user_id']]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
}
?>