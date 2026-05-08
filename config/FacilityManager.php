<?php
/**
 * Facility Manager - Handle health facilities
 * Health Performance Monitoring System
 */

class FacilityManager {
    private $db;
    private $table = 'facilities';

    public function __construct($db) {
        $this->db = $db;
    }

    private function normalizeFacilityNames($facility_names) {
        if (empty($facility_names)) {
            return [];
        }
        if (!is_array($facility_names)) {
            $facility_names = array_map('trim', explode(',', $facility_names));
        }
        return array_values(array_filter(array_unique($facility_names), fn($name) => $name !== ''));
    }

    /**
     * Create facility
     */
    public function createFacility($data) {
        try {
            if (empty($data['name'])) {
                return ['success' => false, 'message' => 'Facility name is required'];
            }

            $query = "INSERT INTO " . $this->table . " 
                      (name, facility_type, province, municipality, address, contact_person, contact_email, contact_phone, color_legend, creator) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($query);
            
            $result = $stmt->execute([
                $data['name'],
                $data['facility_type'] ?? null,
                $data['province'] ?? null,
                $data['municipality'] ?? null,
                $data['address'] ?? null,
                $data['contact_person'] ?? null,
                $data['contact_email'] ?? null,
                $data['contact_phone'] ?? null,
                $data['color_legend'] ?? '#2e7d32',
                $data['creator'] ?? null
            ]);

            if ($result) {
                $facility_id = $this->db->lastInsertId();
                return ['success' => true, 'message' => 'Facility created', 'facility_id' => $facility_id];
            }

            return ['success' => false, 'message' => 'Failed to create facility'];

        } catch (PDOException $e) {
            error_log("Create Facility Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Creation failed'];
        }
    }

    /**
     * Get facility by ID (admin - includes inactive)
     */
    public function getFacilityById($facility_id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$facility_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get Facility Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get facility by ID (active only - for evaluation selection)
     */
    public function getFacility($facility_id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = ? AND is_active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$facility_id]);
            
            return $stmt->fetch();

        } catch (PDOException $e) {
            error_log("Get Facility Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all facilities
     */
    public function getAllFacilities($filters = []) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1";
            $params = [];

            if (!empty($filters['province'])) {
                $query .= " AND province = ?";
                $params[] = $filters['province'];
            }

            if (!empty($filters['facility_type'])) {
                $query .= " AND facility_type = ?";
                $params[] = $filters['facility_type'];
            }

            if (!empty($filters['search'])) {
                $query .= " AND (name LIKE ? OR municipality LIKE ?)";
                $search_term = '%' . $filters['search'] . '%';
                $params[] = $search_term;
                $params[] = $search_term;
            }

            $query .= " ORDER BY name ASC";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Get Facilities Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all facilities including inactive (for admin)
     */
    public function getAllFacilitiesForAdmin() {
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY name ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get Facilities Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get facilities for user evaluation access (creator, editor, or facility_holder)
     */
    public function getFacilitiesForUserEvaluation($user_id, $facility_name = null) {
        try {
            $facility_names = $this->normalizeFacilityNames($facility_name);
            $query = "SELECT * FROM " . $this->table . " WHERE (creator = ? OR editor = ?";
            $params = [$user_id, $user_id];
            
            if (!empty($facility_names)) {
                $query .= " OR name IN (" . implode(',', array_fill(0, count($facility_names), '?')) . ")";
                $params = array_merge($params, $facility_names);
            }
            
            $query .= ") AND is_active = 1 ORDER BY name ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get Facilities for User Evaluation Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get facilities for a specific user (created or edited by them, or matching their facility_name)
     */
    public function getFacilitiesForUser($user_id, $facility_name = null) {
        try {
            $facility_names = $this->normalizeFacilityNames($facility_name);
            $query = "SELECT * FROM " . $this->table . " WHERE (creator = ? OR editor = ?";
            $params = [$user_id, $user_id];
            
            if (!empty($facility_names)) {
                $query .= " OR name IN (" . implode(',', array_fill(0, count($facility_names), '?')) . ")";
                $params = array_merge($params, $facility_names);
            }
            
            $query .= ") ORDER BY name ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get Facilities for User Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update facility
     */
    public function updateFacility($facility_id, $data) {
        try {
            $allowed_fields = ['name', 'facility_type', 'province', 'municipality', 'address', 'contact_person', 'contact_email', 'contact_phone', 'creator', 'editor', 'color_legend'];
            
            $update_fields = [];
            $params = [];

            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $update_fields[] = $field . " = ?";
                    $params[] = $data[$field];
                }
            }

            if (empty($update_fields)) {
                return ['success' => false, 'message' => 'No fields to update'];
            }

            $params[] = $facility_id;

            $query = "UPDATE " . $this->table . " SET " . implode(", ", $update_fields) . " WHERE id = ?";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($params);

            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Facility updated'];
            }

            return ['success' => false, 'message' => 'Update failed'];

        } catch (PDOException $e) {
            error_log("Update Facility Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Update failed'];
        }
    }

    /**
     * Delete facility (soft delete)
     */
    public function deleteFacility($facility_id) {
        try {
            $query = "UPDATE " . $this->table . " SET is_active = 0 WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$facility_id]);

            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Facility deleted'];
            }

            return ['success' => false, 'message' => 'Delete failed'];

        } catch (PDOException $e) {
            error_log("Delete Facility Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Delete failed'];
        }
    }

    /**
     * Check if user has access to view/edit facility
     */
    public function canUserAccessFacility($facility_id, $user_id, $facility_name = null) {
        try {
            $facility = $this->getFacilityById($facility_id);
            if (!$facility) {
                return false;
            }
            
            // User created or edited the facility
            if ($facility['creator'] == $user_id || $facility['editor'] == $user_id) {
                return true;
            }
            
            // Facility name matches one of the user's assigned facilities
            if (in_array($facility['name'], $this->normalizeFacilityNames($facility_name), true)) {
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Check Facility Access Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's relationships to a facility (multiple purposes possible)
     */
    public function getUserFacilityRelationships($facility_id, $user_id, $facility_name = null) {
        try {
            $facility = $this->getFacilityById($facility_id);
            if (!$facility) {
                return [];
            }
            
            $relationships = [];
            
            // Check if user created (owned) the facility
            if ($facility['creator'] == $user_id) {
                $relationships[] = 'owned';
            }
            
            // Check if user edited the facility
            if ($facility['editor'] == $user_id) {
                $relationships[] = 'edited';
            }
            
            // Check if facility matches one of the user's assigned facilities
            if (in_array($facility['name'], $this->normalizeFacilityNames($facility_name), true)) {
                $relationships[] = 'holder';
            }
            
            return $relationships;
        } catch (PDOException $e) {
            error_log("Get User Facility Relationships Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get facility statistics
     */
    public function getFacilityStats($facility_id) {
        try {
            $stats = [];

            // Total evaluations
            $query = "SELECT COUNT(*) as total_evaluations FROM evaluations WHERE facility_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$facility_id]);
            $stats['total_evaluations'] = $stmt->fetch()['total_evaluations'] ?? 0;

            // Completed evaluations
            $query = "SELECT COUNT(*) as completed FROM evaluations WHERE facility_id = ? AND status = 'completed'";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$facility_id]);
            $stats['completed_evaluations'] = $stmt->fetch()['completed'] ?? 0;

            // Approved evaluations
            $query = "SELECT COUNT(*) as approved FROM evaluations WHERE facility_id = ? AND status = 'approved'";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$facility_id]);
            $stats['approved_evaluations'] = $stmt->fetch()['approved'] ?? 0;

            return $stats;

        } catch (PDOException $e) {
            error_log("Get Facility Stats Error: " . $e->getMessage());
            return [];
        }
    }
}
?>
