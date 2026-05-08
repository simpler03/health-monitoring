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
        $this->initializeUserFacilitiesTable();
        $this->backfillLegacyUserFacilities();
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

    private function initializeUserFacilitiesTable() {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS user_facilities (
                    user_id INT NOT NULL,
                    facility_id INT NOT NULL,
                    assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (user_id, facility_id),
                    KEY idx_user_facilities_user (user_id),
                    KEY idx_user_facilities_facility (facility_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ");
        } catch (PDOException $e) {
            error_log("Initialize User Facilities Error: " . $e->getMessage());
        }
    }

    private function backfillLegacyUserFacilities() {
        try {
            $this->db->exec("
                INSERT IGNORE INTO user_facilities (user_id, facility_id)
                SELECT u.id, f.id
                FROM users u
                JOIN facilities f ON f.name = u.facility_name
                WHERE u.facility_name IS NOT NULL AND u.facility_name != ''
            ");
        } catch (PDOException $e) {
            error_log("Backfill User Facilities Error: " . $e->getMessage());
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

            $user_id = (int)$this->db->lastInsertId();
            $this->syncUserFacilities($user_id, $data['facility_ids'] ?? []);

            return ['success' => true, 'message' => 'User registered successfully', 'user_id' => $user_id];

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
                $facility_names = $this->getUserFacilityNames($user['id']);
                if (empty($facility_names) && !empty($user['facility_name'])) {
                    $facility_names = [$user['facility_name']];
                }
                $_SESSION['facility_names'] = $facility_names;
                $_SESSION['facility_name'] = $facility_names[0] ?? $user['facility_name'];
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
            $user = $stmt->fetch();
            if ($user) {
                $user['facility_ids'] = $this->getUserFacilityIds($user_id);
                $user['facility_names'] = $this->getUserFacilityNames($user_id);
                if (!empty($user['facility_name'])) {
                    $legacy_ids = $this->getFacilityIdsByNames([$user['facility_name']]);
                    $user['facility_ids'] = array_values(array_unique(array_merge($user['facility_ids'], $legacy_ids)));
                    if (empty($user['facility_names']) || !empty($legacy_ids)) {
                        $user['facility_names'] = array_values(array_unique(array_merge($user['facility_names'], [$user['facility_name']])));
                    }
                }
            }
            return $user;
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
            $user_id = (int)$user_id;
            if (empty($data['username']) || empty($data['email']) || empty($data['full_name'])) {
                return ['success' => false, 'message' => 'Username, email, and full name are required'];
            }
            $conflict = $this->findUserConflict($data['username'], $data['email'], $user_id);
            if ($conflict) {
                return ['success' => false, 'message' => ucfirst($conflict) . ' already in use'];
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
            $this->syncUserFacilities($user_id, $data['facility_ids'] ?? []);
            return ['success' => true, 'message' => 'User updated'];
        } catch (PDOException $e) {
            error_log("Update User Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Update failed'];
        }
    }

    public function syncUserFacilities($user_id, $facility_ids) {
        try {
            $facility_ids = array_values(array_unique(array_filter(array_map('intval', (array)$facility_ids))));
            if (!empty($facility_ids)) {
                $valid_stmt = $this->db->prepare("SELECT id FROM facilities WHERE id IN (" . implode(',', array_fill(0, count($facility_ids), '?')) . ")");
                $valid_stmt->execute($facility_ids);
                $valid_ids = array_map('intval', $valid_stmt->fetchAll(PDO::FETCH_COLUMN));
                $facility_ids = array_values(array_filter($facility_ids, fn($id) => in_array($id, $valid_ids, true)));
            }

            $this->db->beginTransaction();

            $delete = $this->db->prepare("DELETE FROM user_facilities WHERE user_id = ?");
            $delete->execute([$user_id]);

            if (!empty($facility_ids)) {
                $insert = $this->db->prepare("INSERT INTO user_facilities (user_id, facility_id) VALUES (?, ?)");
                foreach ($facility_ids as $facility_id) {
                    $insert->execute([$user_id, $facility_id]);
                }
            }

            $primary_name = null;
            if (!empty($facility_ids)) {
                $name_stmt = $this->db->prepare("SELECT name FROM facilities WHERE id = ? LIMIT 1");
                $name_stmt->execute([$facility_ids[0]]);
                $primary_name = $name_stmt->fetchColumn() ?: null;
            }

            $legacy = $this->db->prepare("UPDATE " . $this->table . " SET facility_name = ? WHERE id = ?");
            $legacy->execute([$primary_name, $user_id]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Sync User Facilities Error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserFacilityIds($user_id) {
        try {
            $query = "SELECT facility_id FROM user_facilities WHERE user_id = ? ORDER BY assigned_at ASC, facility_id ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$user_id]);
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (PDOException $e) {
            error_log("Get User Facility IDs Error: " . $e->getMessage());
            return [];
        }
    }

    public function getUserFacilityNames($user_id) {
        try {
            $query = "SELECT f.name
                      FROM user_facilities uf
                      JOIN facilities f ON uf.facility_id = f.id
                      WHERE uf.user_id = ?
                      ORDER BY f.name ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Get User Facility Names Error: " . $e->getMessage());
            return [];
        }
    }

    private function getFacilityIdsByNames($facility_names) {
        try {
            $facility_names = array_values(array_filter((array)$facility_names));
            if (empty($facility_names)) {
                return [];
            }
            $query = "SELECT id FROM facilities WHERE name IN (" . implode(',', array_fill(0, count($facility_names), '?')) . ")";
            $stmt = $this->db->prepare($query);
            $stmt->execute($facility_names);
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (PDOException $e) {
            error_log("Get Facility IDs By Names Error: " . $e->getMessage());
            return [];
        }
    }

    public function formatUserFacilities($user_id, $fallback = null) {
        $facility_names = $this->getUserFacilityNames($user_id);
        if (empty($facility_names) && !empty($fallback)) {
            $facility_names = [$fallback];
        }
        return !empty($facility_names) ? implode(', ', $facility_names) : '-';
    }

    /**
     * Check if user exists excluding a user ID
     */
    private function userExistsExcluding($username, $email, $exclude_id) {
        return (bool)$this->findUserConflict($username, $email, $exclude_id);
    }

    private function findUserConflict($username, $email, $exclude_id) {
        try {
            $exclude_id = (int)$exclude_id;

            $query = "SELECT id FROM " . $this->table . " WHERE username = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$username]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $existing_id) {
                if ((int)$existing_id !== $exclude_id) {
                    return 'username';
                }
            }

            $query = "SELECT id FROM " . $this->table . " WHERE email = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$email]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $existing_id) {
                if ((int)$existing_id !== $exclude_id) {
                    return 'email';
                }
            }

            return null;
        } catch (PDOException $e) {
            error_log("User Conflict Check Error: " . $e->getMessage());
            return null;
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
