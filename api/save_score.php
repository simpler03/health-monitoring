<?php
/**
 * AJAX API Endpoint - Save Score
 * Handles real-time score updates via AJAX
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/Database.php';
require_once '../config/Auth.php';
require_once '../config/EvaluationManager.php';
require_once '../config/FacilityManager.php';

try {
    // Check if user is logged in
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        $input = $_POST;
    }

    // Validate required parameters
    if (!isset($input['evaluation_id']) || !isset($input['kpi_id']) || !isset($input['score_value'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }

    // Sanitize input
    $evaluation_id = (int)$input['evaluation_id'];
    $kpi_id = (int)$input['kpi_id'];
    $score_value = isset($input['score_value']) && $input['score_value'] !== '' ? (float)$input['score_value'] : null;
    $percentage_value = isset($input['percentage_value']) && $input['percentage_value'] !== '' ? (float)$input['percentage_value'] : null;
    $remarks = isset($input['remarks']) ? substr($input['remarks'], 0, 500) : '';

    // Additional validation
    if ($percentage_value !== null && ($percentage_value < 0 || $percentage_value > 100)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Percentage must be between 0 and 100']);
        exit;
    }

    // Initialize database and manager
    $database = new Database();
    $db = $database->connect();
    $manager = new EvaluationManager($db);
    $facility_manager = new FacilityManager($db);

    // Verify user has access to this evaluation (could be more sophisticated)
    $evaluation = $manager->getEvaluation($evaluation_id);
    if (!$evaluation) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Evaluation not found']);
        exit;
    }

    // Check permission based on role
    $user_role = $_SESSION['role'] ?? 'viewer';
    if ($user_role === 'viewer') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        exit;
    }

    // Facility access control: check if user can access this evaluation's facility
    // User must be creator, editor, or holder of the facility
    if ($user_role !== 'admin' && in_array($user_role, ['viewer', 'input'])) {
        $user_id = $_SESSION['user_id'] ?? null;
        $user_facility = $_SESSION['facility_names'] ?? ($_SESSION['facility_name'] ?? null);
        if (!$facility_manager->canUserAccessFacility($evaluation['facility_id'], $user_id, $user_facility)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied: evaluation is not in an accessible facility']);
            exit;
        }
    }

    // Check if evaluation is already completed
    if ($evaluation['status'] === 'completed') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Evaluation is already completed and cannot be edited']);
        exit;
    }

    // Get old score before saving
    $old_score = $manager->getScoreByKPI($evaluation_id, $kpi_id);
    $old_value = $old_score ? ($old_score['score_value'] ?? null) : null;

    // Save the score
    $result = $manager->saveScore($evaluation_id, $kpi_id, $score_value, $percentage_value, $remarks);

    // Log to audit_logs if successfully saved and value changed
    if ($result['success'] && $old_value !== $score_value) {
        try {
            // Get full context: facility, building block, objective, and indicator
            $context_query = "SELECT 
                                f.name as facility_name,
                                bb.name as building_block_name,
                                so.name as objective_name,
                                k.indicator_name
                              FROM evaluations e
                              JOIN facilities f ON e.facility_id = f.id
                              JOIN key_performance_indicators k ON k.id = ?
                              JOIN strategic_objectives so ON k.strategic_objective_id = so.id
                              JOIN building_blocks bb ON so.building_block_id = bb.id
                              WHERE e.id = ?";
            $context_stmt = $db->prepare($context_query);
            $context_stmt->execute([$kpi_id, $evaluation_id]);
            $context = $context_stmt->fetch();

            if ($context) {
                $facility_name = htmlspecialchars($context['facility_name']);
                $bb_name = htmlspecialchars($context['building_block_name']);
                $so_name = htmlspecialchars($context['objective_name']);
                $kpi_name = htmlspecialchars($context['indicator_name']);
            } else {
                $facility_name = 'Unknown';
                $bb_name = 'Unknown';
                $so_name = 'Unknown';
                $kpi_name = 'Indicator #' . $kpi_id;
            }

            // Format old and new values for logging
            $old_display = $old_value !== null ? $old_value : 'empty';
            $new_display = $score_value !== null ? $score_value : 'empty';
            
            // Format: "facility: X | building block: Y | objective: Z | indicator: Name (0 -> 1)"
            $change_string = "facility: " . $facility_name . "\n"
                           . "building block: " . $bb_name . "\n"
                           . "objective: " . $so_name . "\n"
                           . "indicator: " . $kpi_name . " (" . $old_display . " -> " . $new_display . ")";
            
            $audit_query = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_value, new_value, ip_address, user_agent) 
                           VALUES (?, 'UPDATE_EVALUATION', 'USER', ?, ?, ?, ?, ?)";
            $audit_stmt = $db->prepare($audit_query);
            $audit_stmt->execute([
                $_SESSION['user_id'],
                $_SESSION['user_id'],
                null,
                $change_string,
                $_SERVER['REMOTE_ADDR'] ?? '::1',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (Exception $audit_error) {
            error_log("Audit log error in save_score: " . $audit_error->getMessage());
            // Don't fail the score save if audit logging fails
        }
    }

    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);

} catch (Exception $e) {
    error_log("Save Score API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>
