<?php
/**
 * Authentication and Session Management
 * Health Performance Monitoring System
 */

class Auth {
    private $db;
    private $table = 'users';
    private $session_table = 'sessions';

    public function __construct($db) {
        $this->db = $db;
        $this->initializeSession();
    }

    /**
     * Initialize session with security measures
     */
    private function initializeSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            
            // Set secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
            ini_set('session.cookie_samesite', 'Strict');
        }
    }

    /**
     * Register new user
     */
    public function register($data) {
        try {
            // Validate input
            if (empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['full_name'])) {
                return ['success' => false, 'message' => 'All fields are required'];
            }

            // Check if user exists
            if ($this->userExists($data['username'], $data['email'])) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }

            // Hash password
            $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);

            // Prepare and execute insert query
            $query = "INSERT INTO " . $this->table . " 
                      (username, email, password_hash, full_name, role, facility_name) 
                      VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($query);
            
            $stmt->execute([
                $data['username'],
                $data['email'],
                $password_hash,
                $data['full_name'],
                $data['role'] ?? 'viewer',
                $data['facility_name'] ?? null
            ]);

            return ['success' => true, 'message' => 'User registered successfully'];

        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    /**
     * Login user
     */
    public function login($username, $password) {
        try {
            if (empty($username) || empty($password)) {
                return ['success' => false, 'message' => 'Username and password are required'];
            }

            // Get user from database
            $query = "SELECT id, username, email, password_hash, role, full_name, facility_name, is_active 
                      FROM " . $this->table . " 
                      WHERE (username = ? OR email = ?) AND is_active = 1";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['facility_name'] = $user['facility_name'];
                $_SESSION['login_time'] = time();

                // Store session in database
                $session_token = session_id();
                $query = "INSERT INTO " . $this->session_table . " (user_id, session_token, ip_address, user_agent, expires_at) 
                          VALUES (?, ?, ?, ?, ?) 
                          ON DUPLICATE KEY UPDATE expires_at = VALUES(expires_at), ip_address = VALUES(ip_address), user_agent = VALUES(user_agent)";
                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    $user['id'], 
                    $session_token, 
                    $_SERVER['REMOTE_ADDR'] ?? null, 
                    $_SERVER['HTTP_USER_AGENT'] ?? null, 
                    date('Y-m-d H:i:s', time() + 86400) // 24 hours
                ]);

                // Log audit trail
                $this->logAuditTrail($_SESSION['user_id'], 'LOGIN', 'USER', $user['id']);

                return ['success' => true, 'message' => 'Login successful'];
            }

            return ['success' => false, 'message' => 'Invalid credentials'];

        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed'];
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            // Remove session from database
            $session_token = session_id();
            $query = "DELETE FROM " . $this->session_table . " WHERE session_token = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$session_token]);

            $this->logAuditTrail($_SESSION['user_id'], 'LOGOUT', 'USER', $_SESSION['user_id']);
        }
        
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user
     */
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return $_SESSION;
        }
        return null;
    }

    /**
     * Check if user is online (has active session)
     */
    public function isUserOnline($user_id) {
        try {
            $query = "SELECT id FROM " . $this->session_table . " WHERE user_id = ? AND expires_at > NOW()";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$user_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Check Online Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check user role
     */
    public function hasRole($required_role) {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $user_role = $_SESSION['role'] ?? null;
        
        if ($required_role === 'admin') {
            return $user_role === 'admin';
        } elseif ($required_role === 'input') {
            return in_array($user_role, ['admin', 'input']);
        }
        
        return true; // Viewer role for everyone
    }

    /**
     * Get user by ID (for admin - excludes password for display)
     */
    public function getUserById($user_id) {
        try {
            $query = "SELECT id, username, email, full_name, role, facility_name, is_active, created_at FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$user_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get User Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update user
     */
    public function updateUser($user_id, $data) {
        try {
            if (empty($data['username']) || empty($data['email']) || empty($data['full_name'])) {
                return ['success' => false, 'message' => 'Username, email, and full name are required'];
            }
            if ($this->userExistsExcluding($data['username'], $data['email'], $user_id)) {
                return ['success' => false, 'message' => 'Username or email already in use'];
            }
            $params = [$data['username'], $data['email'], $data['full_name'], $data['role'] ?? 'viewer', $data['facility_name'] ?? null, $data['is_active'] ?? 1];
            $query = "UPDATE " . $this->table . " SET username=?, email=?, full_name=?, role=?, facility_name=?, is_active=?";
            if (!empty($data['password'])) {
                $query .= ", password_hash=?";
                $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
            }
            $query .= " WHERE id=?";
            $params[] = $user_id;
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return ['success' => true, 'message' => 'User updated'];
        } catch (PDOException $e) {
            error_log("Update User Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Update failed'];
        }
    }

    /**
     * Check if user exists excluding a user ID
     */
    private function userExistsExcluding($username, $email, $exclude_id) {
        try {
            $query = "SELECT id FROM " . $this->table . " WHERE (username = ? OR email = ?) AND id != ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$username, $email, $exclude_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Check if user exists
     */
    private function userExists($username, $email) {
        try {
            $query = "SELECT id FROM " . $this->table . " WHERE username = ? OR email = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$username, $email]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("User Check Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log audit trail
     */
    public function logAuditTrail($user_id, $action, $entity_type, $entity_id, $old_value = null, $new_value = null) {
        try {
            $query = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_value, new_value, ip_address, user_agent) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $user_id,
                $action,
                $entity_type,
                $entity_id,
                $old_value,
                $new_value,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Audit Log Error: " . $e->getMessage());
        }
    }

    /**
     * Get current user role
     */
    public function getCurrentRole() {
        return $_SESSION['role'] ?? 'viewer';
    }

    /**
     * Require login (redirect if not logged in)
     */
    public static function requireLogin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit;
        }
    }

    /**
     * Require specific role
     */
    public static function requireRole($role) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit;
        }

        $user_role = $_SESSION['role'] ?? 'viewer';
        
        if ($role === 'admin' && $user_role !== 'admin') {
            header("Location: unauthorized.php");
            exit;
        } elseif ($role === 'input' && !in_array($user_role, ['admin', 'input'])) {
            header("Location: unauthorized.php");
            exit;
        }
    }
}
?>
