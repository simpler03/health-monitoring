<?php

class QueryHelper {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Get all evaluations for a facility with building block summaries
     */
    public function getFacilityEvaluations($facility_id) {
        $query = "
            SELECT e.*, f.name as facility_name, f.province, u.full_name as evaluator_name
            FROM evaluations e
            JOIN facilities f ON e.facility_id = f.id
            LEFT JOIN users u ON e.created_by = u.id
            WHERE e.facility_id = ?
            ORDER BY e.created_at DESC
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$facility_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all building blocks with their data
     */
    public function getBuildingBlocks() {
        $query = "
            SELECT * FROM building_blocks 
            WHERE is_active = 1
            ORDER BY sort_order ASC
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get strategic objectives for a building block
     */
    public function getStrategicObjectives($building_block_id) {
        $query = "
            SELECT * FROM strategic_objectives 
            WHERE building_block_id = ? AND is_active = 1
            ORDER BY sort_order ASC
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$building_block_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get KPIs for strategic objectives (group by building block)
     */
    public function getKPIsByBuildingBlock($building_block_id) {
        $query = "
            SELECT kpi.*, so.id as strategic_objective_id, so.name as objective_name
            FROM key_performance_indicators kpi
            JOIN strategic_objectives so ON kpi.strategic_objective_id = so.id
            WHERE so.building_block_id = ? AND kpi.is_active = 1
            ORDER BY so.sort_order ASC, kpi.sort_order ASC
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$building_block_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all scores for an evaluation
     */
    public function getEvaluationScores($evaluation_id) {
        $query = "
            SELECT s.*, kpi.id as kpi_id, kpi.indicator_name, kpi.code
            FROM scores s
            JOIN key_performance_indicators kpi ON s.key_performance_indicator_id = kpi.id
            WHERE s.evaluation_id = ?
            ORDER BY kpi.sort_order ASC
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$evaluation_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get building block statistics for an evaluation
     */
    public function getBuildingBlockStats($evaluation_id, $building_block_id) {
        $query = "
            SELECT 
                bb.id,
                bb.name as building_block_name,
                bb.weight_percentage,
                COUNT(s.id) as total_indicators,
                SUM(CASE WHEN s.percentage_value >= 50 THEN 1 ELSE 0 END) as complied_count,
                ROUND(COALESCE(AVG(s.percentage_value), 0), 2) as average_score
            FROM building_blocks bb
            LEFT JOIN strategic_objectives so ON bb.id = so.building_block_id
            LEFT JOIN key_performance_indicators kpi ON so.id = kpi.strategic_objective_id
            LEFT JOIN scores s ON kpi.id = s.key_performance_indicator_id AND s.evaluation_id = ?
            WHERE bb.id = ? AND bb.is_active = 1
            GROUP BY bb.id, bb.name, bb.weight_percentage
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$evaluation_id, $building_block_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all building block stats for an evaluation
     */
    public function getAllBuildingBlockStats($evaluation_id) {
        $query = "
            SELECT 
                bb.id,
                bb.name as building_block_name,
                bb.code,
                bb.weight_percentage,
                COUNT(DISTINCT s.id) as total_indicators,
                SUM(CASE WHEN s.percentage_value >= 50 THEN 1 ELSE 0 END) as complied_count,
                ROUND(COALESCE(AVG(s.percentage_value), 0), 2) as average_score
            FROM building_blocks bb
            LEFT JOIN strategic_objectives so ON bb.id = so.building_block_id
            LEFT JOIN key_performance_indicators kpi ON so.id = kpi.strategic_objective_id
            LEFT JOIN scores s ON kpi.id = s.key_performance_indicator_id AND s.evaluation_id = ?
            WHERE bb.is_active = 1
            GROUP BY bb.id, bb.name, bb.code, bb.weight_percentage
            ORDER BY bb.sort_order ASC
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$evaluation_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate facility overall compliance score
     */
    public function calculateFacilityScore($evaluation_id) {
        $query = "
            SELECT 
                COUNT(DISTINCT s.id) as total_scores,
                SUM(CASE WHEN s.percentage_value >= 50 THEN 1 ELSE 0 END) as complied_scores,
                ROUND(COALESCE(AVG(s.percentage_value), 0), 2) as overall_percentage
            FROM scores s
            WHERE s.evaluation_id = ?
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$evaluation_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get performance rating based on percentage score
     */
    public function getPerformanceRating($percentage) {
        if ($percentage >= 90) {
            return ['rating' => 'Excellent', 'color' => '#2e7d32'];
        } elseif ($percentage >= 80) {
            return ['rating' => 'Very Good', 'color' => '#558b2f'];
        } elseif ($percentage >= 70) {
            return ['rating' => 'Good', 'color' => '#f57c00'];
        } elseif ($percentage >= 60) {
            return ['rating' => 'Satisfactory', 'color' => '#ff9800'];
        } else {
            return ['rating' => 'Needs Improvement', 'color' => '#c62828'];
        }
    }

    /**
     * Get provincial statistics by building block
     */
    public function getProvinceStatsByBuildingBlock($province, $evaluation_date = null) {
        $query = "
            SELECT 
                bb.id,
                bb.name as building_block_name,
                bb.code,
                COUNT(DISTINCT f.id) as total_facilities,
                COUNT(DISTINCT CASE WHEN s.percentage_value >= 50 THEN f.id END) as facilities_complied,
                COUNT(DISTINCT s.id) as total_indicators,
                SUM(CASE WHEN s.percentage_value >= 50 THEN 1 ELSE 0 END) as indicators_complied,
                ROUND(COALESCE(AVG(s.percentage_value), 0), 2) as average_score
            FROM building_blocks bb
            LEFT JOIN strategic_objectives so ON bb.id = so.building_block_id
            LEFT JOIN key_performance_indicators kpi ON so.id = kpi.strategic_objective_id
            LEFT JOIN scores s ON kpi.id = s.key_performance_indicator_id
            LEFT JOIN evaluations e ON s.evaluation_id = e.id
            LEFT JOIN facilities f ON e.facility_id = f.id
            WHERE f.province = ? AND bb.is_active = 1
        ";
        
        $params = [$province];
        
        if ($evaluation_date) {
            $query .= " AND DATE(e.created_at) = ?";
            $params[] = $evaluation_date;
        }
        
        $query .= " GROUP BY bb.id, bb.name, bb.code, bb.weight_percentage
                    ORDER BY bb.sort_order ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all unique provinces with evaluations
     */
    public function getAllProvinces() {
        $query = "
            SELECT DISTINCT f.province 
            FROM facilities f
            WHERE f.province IS NOT NULL AND f.province != ''
            ORDER BY f.province ASC
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get evaluation details with facility info
     */
    public function getEvaluationDetails($evaluation_id) {
        $query = "
            SELECT 
                e.*,
                f.name as facility_name,
                f.facility_type as facility_type,
                f.province,
                f.municipality,
                u.full_name as evaluator_name
            FROM evaluations e
            JOIN facilities f ON e.facility_id = f.id
            LEFT JOIN users u ON e.created_by = u.id
            WHERE e.id = ?
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$evaluation_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Count evaluations per facility
     */
    public function getEvaluationCount($facility_id = null) {
        if ($facility_id) {
            $query = "SELECT COUNT(*) as count FROM evaluations WHERE facility_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$facility_id]);
        } else {
            $query = "SELECT COUNT(*) as count FROM evaluations";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
}
?>
