<?php
/**
 * Evaluation Manager - Handle evaluations and scoring
 * Health Performance Monitoring System
 */

class EvaluationManager {
    private $db;
    private $evaluations_table = 'evaluations';
    private $scores_table = 'scores';
    private $kpi_table = 'key_performance_indicators';
    private $bb_table = 'building_blocks';

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
     * Create new evaluation and initialize score records with NULL values for all active KPIs
     */
    public function createEvaluation($data) {
        try {
            if (empty($data['facility_id']) || empty($data['evaluation_date'])) {
                return ['success' => false, 'message' => 'Facility and date are required'];
            }

            $query = "INSERT INTO " . $this->evaluations_table . " 
                      (facility_id, evaluation_date, evaluation_period, status, created_by, updated_by) 
                      VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($query);
            
            $result = $stmt->execute([
                $data['facility_id'],
                $data['evaluation_date'],
                $data['evaluation_period'] ?? date('Y-m'),
                'draft',
                $_SESSION['user_id'] ?? 1,
                $_SESSION['user_id'] ?? 1
            ]);

            if ($result) {
                $evaluation_id = $this->db->lastInsertId();
                
                // Initialize score records with NULL values for all active KPIs
                $this->initializeScoresForEvaluation($evaluation_id);
                
                return ['success' => true, 'message' => 'Evaluation created', 'evaluation_id' => $evaluation_id];
            }

            return ['success' => false, 'message' => 'Failed to create evaluation'];

        } catch (PDOException $e) {
            error_log("Create Evaluation Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Creation failed'];
        }
    }

    /**
     * Initialize score records for all active KPIs with NULL values
     * This allows scores to be added later following the key_performance_indicator_id relationship
     */
    public function initializeScoresForEvaluation($evaluation_id) {
        try {
            // Get all active KPIs
            $kpi_query = "SELECT id FROM " . $this->kpi_table . " 
                          WHERE is_active = 1";
            
            $kpi_stmt = $this->db->prepare($kpi_query);
            $kpi_stmt->execute();
            $kpis = $kpi_stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($kpis)) {
                return;
            }

            // Insert score record for each KPI with NULL values
            $insert_query = "INSERT INTO " . $this->scores_table . " 
                            (evaluation_id, key_performance_indicator_id, score_value, percentage_value, remarks) 
                            VALUES (?, ?, NULL, NULL, '')";
            
            $insert_stmt = $this->db->prepare($insert_query);
            
            foreach ($kpis as $kpi) {
                try {
                    $insert_stmt->execute([
                        $evaluation_id,
                        $kpi['id']
                    ]);
                } catch (PDOException $e) {
                    // Skip if score already exists (duplicate key)
                    error_log("Score initialization error for KPI " . $kpi['id'] . ": " . $e->getMessage());
                }
            }

        } catch (PDOException $e) {
            error_log("Initialize Scores Error: " . $e->getMessage());
        }
    }

    /**
     * Get evaluation by ID
     */
    public function getEvaluation($evaluation_id) {
        try {
            $query = "SELECT e.*, f.name as facility_name 
                      FROM " . $this->evaluations_table . " e
                      JOIN facilities f ON e.facility_id = f.id
                      WHERE e.id = ?";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$evaluation_id]);
            
            return $stmt->fetch();

        } catch (PDOException $e) {
            error_log("Get Evaluation Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all evaluations with filters
     */
    public function getEvaluations($filters = []) {
        try {
            $query = "SELECT e.*, f.name as facility_name, u.full_name as created_by_name
                      FROM " . $this->evaluations_table . " e
                      JOIN facilities f ON e.facility_id = f.id
                      LEFT JOIN users u ON e.created_by = u.id
                      WHERE 1=1";

            $params = [];

            if (!empty($filters['facility_id'])) {
                $query .= " AND e.facility_id = ?";
                $params[] = $filters['facility_id'];
            }

            if (!empty($filters['status'])) {
                $query .= " AND e.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['year'])) {
                // use the evaluation_date column (YYYY-MM-DD) with a right-side wildcard
                $query .= " AND e.evaluation_date LIKE ?";
                $params[] = $filters['year'] . '-%';
            }

            if (!empty($filters['date_from'])) {
                $query .= " AND e.evaluation_date >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $query .= " AND e.evaluation_date <= ?";
                $params[] = $filters['date_to'];
            }

            $query .= " ORDER BY e.evaluation_date DESC, e.created_at DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Get Evaluations Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get evaluations for user with facility access control (creator, editor, or facility_holder)
     */
    public function getEvaluationsForUser($user_id, $facility_name = null, $filters = []) {
        try {
            $facility_names = $this->normalizeFacilityNames($facility_name);
            $query = "SELECT e.*, f.name as facility_name, u.full_name as created_by_name
                      FROM " . $this->evaluations_table . " e
                      JOIN facilities f ON e.facility_id = f.id
                      LEFT JOIN users u ON e.created_by = u.id
                      WHERE (f.creator = ? OR f.editor = ?";
            
            $params = [$user_id, $user_id];
            
            if (!empty($facility_names)) {
                $query .= " OR f.name IN (" . implode(',', array_fill(0, count($facility_names), '?')) . ")";
                $params = array_merge($params, $facility_names);
            }

            $query .= ")";

            if (!empty($filters['year'])) {
                $query .= " AND e.evaluation_date LIKE ?";
                $params[] = $filters['year'] . '-%';
            }
            
            $query .= " ORDER BY e.evaluation_date DESC, e.created_at DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Get Evaluations for User Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update evaluation status
     */
    public function updateStatus($evaluation_id, $status) {
        try {
            $allowed_statuses = ['draft', 'in_progress', 'completed', 'approved'];
            
            if (!in_array($status, $allowed_statuses)) {
                return ['success' => false, 'message' => 'Invalid status'];
            }

            $query = "UPDATE " . $this->evaluations_table . " 
                      SET status = ?, updated_by = ?, updated_at = NOW() 
                      WHERE id = ?";

            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$status, $_SESSION['user_id'] ?? 1, $evaluation_id]);

            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Status updated'];
            }

            return ['success' => false, 'message' => 'Update failed'];

        } catch (PDOException $e) {
            error_log("Update Status Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Update failed'];
        }
    }

    /**
     * Save score for an indicator
     */
    public function saveScore($evaluation_id, $kpi_id, $score_value, $percentage_value = null, $remarks = '') {
        try {
            if (!is_numeric($evaluation_id) || !is_numeric($kpi_id)) {
                return ['success' => false, 'message' => 'Invalid parameters'];
            }

            // Validate score value
            if (!is_null($score_value) && !is_numeric($score_value)) {
                return ['success' => false, 'message' => 'Score must be numeric'];
            }

            // Validate percentage
            if (!is_null($percentage_value) && (!is_numeric($percentage_value) || $percentage_value < 0 || $percentage_value > 100)) {
                return ['success' => false, 'message' => 'Percentage must be between 0 and 100'];
            }

            // Check if score exists
            $check_query = "SELECT id FROM " . $this->scores_table . " 
                            WHERE evaluation_id = ? AND key_performance_indicator_id = ?";
            $check_stmt = $this->db->prepare($check_query);
            $check_stmt->execute([$evaluation_id, $kpi_id]);
            $existing = $check_stmt->fetch();

            if ($existing) {
                // Update existing score
                $query = "UPDATE " . $this->scores_table . " 
                          SET score_value = ?, percentage_value = ?, remarks = ?, last_edited_by = ?, last_edited_at = NOW() 
                          WHERE evaluation_id = ? AND key_performance_indicator_id = ?";
            } else {
                // Insert new score
                $query = "INSERT INTO " . $this->scores_table . " 
                          (evaluation_id, key_performance_indicator_id, score_value, percentage_value, remarks, last_edited_by) 
                          VALUES (?, ?, ?, ?, ?, ?)";
            }

            $stmt = $this->db->prepare($query);

            if ($existing) {
                $result = $stmt->execute([
                    $score_value,
                    $percentage_value,
                    $remarks,
                    $_SESSION['user_id'] ?? 1,
                    $evaluation_id,
                    $kpi_id
                ]);
            } else {
                $result = $stmt->execute([
                    $evaluation_id,
                    $kpi_id,
                    $score_value,
                    $percentage_value,
                    $remarks,
                    $_SESSION['user_id'] ?? 1
                ]);
            }

            if ($result) {
                return ['success' => true, 'message' => 'Score saved'];
            }

            return ['success' => false, 'message' => 'Failed to save score'];

        } catch (PDOException $e) {
            error_log("Save Score Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Save failed'];
        }
    }

    /**
     * Get scores for evaluation (only rows that exist in scores table)
     */
    public function getScoresForEvaluation($evaluation_id) {
        try {
            $query = "SELECT s.*, k.indicator_name, k.target_value, k.data_type, 
                             so.name as objective_name, bb.name as building_block_name, bb.weight_percentage
                      FROM " . $this->scores_table . " s
                      JOIN " . $this->kpi_table . " k ON s.key_performance_indicator_id = k.id
                      JOIN strategic_objectives so ON k.strategic_objective_id = so.id
                      JOIN building_blocks bb ON so.building_block_id = bb.id
                      WHERE s.evaluation_id = ?
                      ORDER BY bb.sort_order, so.sort_order, k.sort_order";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$evaluation_id]);
            
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Get Scores Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get score by evaluation_id and key_performance_indicator_id (supports NULL scores)
     * Follows the scores.key_performance_indicator_id relationship
     */
    public function getScoreByKPI($evaluation_id, $kpi_id) {
        try {
            $query = "SELECT s.*, k.indicator_name, k.target_value, k.data_type, 
                             so.name as objective_name, bb.name as building_block_name
                      FROM " . $this->scores_table . " s
                      JOIN " . $this->kpi_table . " k ON s.key_performance_indicator_id = k.id
                      JOIN strategic_objectives so ON k.strategic_objective_id = so.id
                      JOIN building_blocks bb ON so.building_block_id = bb.id
                      WHERE s.evaluation_id = ? AND s.key_performance_indicator_id = ?";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$evaluation_id, $kpi_id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Get Score by KPI Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all scores for evaluation including NULL values (follows scores.key_performance_indicator_id relationship)
     */
    public function getAllScoresForEvaluation($evaluation_id) {
        try {
            $query = "SELECT s.*, k.indicator_name, k.target_value, k.data_type, 
                             so.name as objective_name, bb.name as building_block_name, bb.weight_percentage
                      FROM " . $this->scores_table . " s
                      JOIN " . $this->kpi_table . " k ON s.key_performance_indicator_id = k.id
                      JOIN strategic_objectives so ON k.strategic_objective_id = so.id
                      JOIN building_blocks bb ON so.building_block_id = bb.id
                      WHERE s.evaluation_id = ?
                      ORDER BY bb.sort_order, so.sort_order, k.sort_order";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$evaluation_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Get All Scores Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all KPIs for an evaluation with optional saved scores (for view/edit display).
     * Returns every active KPI; score_value/percentage_value/remarks come from scores if present.
     */
    public function getIndicatorsForEvaluation($evaluation_id) {
        try {
            $query = "SELECT k.id as key_performance_indicator_id, k.indicator_name, k.target_value, k.means_of_verification,
                             so.name as objective_name, bb.name as building_block_name, bb.weight_percentage,
                             s.score_value, s.percentage_value, s.remarks
                      FROM " . $this->kpi_table . " k
                      JOIN strategic_objectives so ON k.strategic_objective_id = so.id
                      JOIN building_blocks bb ON so.building_block_id = bb.id
                      LEFT JOIN " . $this->scores_table . " s ON s.key_performance_indicator_id = k.id AND s.evaluation_id = ?
                      WHERE k.is_active = 1 AND so.is_active = 1 AND bb.is_active = 1
                      ORDER BY bb.sort_order, so.sort_order, k.sort_order";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$evaluation_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Get Indicators Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate totals and percentages
     */
    public function calculateTotals($evaluation_id) {
        try {
            $scores = $this->getScoresForEvaluation($evaluation_id);
            
            if (empty($scores)) {
                return ['total_score' => 0, 'total_percentage' => 0, 'by_building_block' => []];
            }

            $total_score = 0;
            $total_percentage = 0;
            $score_count = 0;
            $by_building_block = [];

            foreach ($scores as $score) {
                if (!is_null($score['score_value'])) {
                    $total_score += $score['score_value'];
                    $score_count++;
                }

                if (!is_null($score['percentage_value'])) {
                    $total_percentage += $score['percentage_value'];
                }

                $bb_name = $score['building_block_name'];
                if (!isset($by_building_block[$bb_name])) {
                    $by_building_block[$bb_name] = [
                        'weight' => $score['weight_percentage'],
                        'total_score' => 0,
                        'count' => 0,
                        'average' => 0
                    ];
                }

                if (!is_null($score['score_value'])) {
                    $by_building_block[$bb_name]['total_score'] += $score['score_value'];
                    $by_building_block[$bb_name]['count']++;
                }
            }

            // Calculate averages
            foreach ($by_building_block as &$bb) {
                $bb['average'] = $bb['count'] > 0 ? $bb['total_score'] / $bb['count'] : 0;
            }

            $avg_percentage = $score_count > 0 ? $total_percentage / $score_count : 0;

            return [
                'total_score' => round($total_score, 2),
                'average_percentage' => round($avg_percentage, 2),
                'by_building_block' => $by_building_block,
                'score_count' => $score_count
            ];

        } catch (Exception $e) {
            error_log("Calculate Totals Error: " . $e->getMessage());
            return ['total_score' => 0, 'total_percentage' => 0, 'by_building_block' => []];
        }
    }

    /**
     * Update evaluation signatures
     */
    public function updateSignatures($evaluation_id, $signature_data) {
        try {
            $update_fields = [];
            $params = [];

            $allowed_signers = ['assessed', 'verified', 'approved', 'noted', 'conformed'];

            foreach ($allowed_signers as $signer) {
                // use array_key_exists so null/empty values are intentionally saved (cleared)
                if (array_key_exists($signer . '_by', $signature_data)) {
                    $update_fields[] = $signer . "_by = ?";
                    $params[] = $signature_data[$signer . '_by'];
                }
                if (array_key_exists($signer . '_signature', $signature_data)) {
                    $update_fields[] = $signer . "_signature = ?";
                    $params[] = $signature_data[$signer . '_signature'];
                }
                if (array_key_exists($signer . '_date', $signature_data)) {
                    $update_fields[] = $signer . "_date = ?";
                    $params[] = $signature_data[$signer . '_date'];
                }
                if (array_key_exists($signer . '_esignature', $signature_data)) {
                    $update_fields[] = $signer . "_esignature = ?";
                    $params[] = $signature_data[$signer . '_esignature'];
                }
            }

            if (empty($update_fields)) {
                return ['success' => false, 'message' => 'No signature data provided'];
            }

            $update_fields[] = "updated_by = ?";
            $params[] = $_SESSION['user_id'] ?? 1;

            $params[] = $evaluation_id;

            $query = "UPDATE " . $this->evaluations_table . " 
                      SET " . implode(", ", $update_fields) . ", updated_at = NOW()
                      WHERE id = ?";

            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($params);

            if ($result) {
                return ['success' => true, 'message' => 'Signatures updated'];
            }

            return ['success' => false, 'message' => 'Failed to update signatures'];

        } catch (PDOException $e) {
            error_log("Update Signatures Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Update failed'];
        }
    }

    /**
     * Get all building blocks for pagination/tabs
     */
    public function getBuildingBlocks() {
        try {
            $query = "SELECT * FROM " . $this->bb_table . " WHERE is_active = 1 ORDER BY sort_order";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get Building Blocks Error: " . $e->getMessage());
            return [];
        }
    }
}
?>
