<?php
/**
 * AJAX API Endpoint - Get Evaluation Data
 * Returns structured evaluation data for the spreadsheet interface
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST');

require_once __DIR__ . '/../config/BaseConfig.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/EvaluationManager.php';
require_once __DIR__ . '/../config/FacilityManager.php';

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

    // Get evaluation ID from query parameter
    $evaluation_id = isset($_GET['evaluation_id']) ? (int)$_GET['evaluation_id'] : null;

    if (!$evaluation_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Evaluation ID is required']);
        exit;
    }

    // Initialize database and manager
    $database = new Database();
    $db = $database->connect();
    $manager = new EvaluationManager($db);
    $facility_manager = new FacilityManager($db);

    // Get evaluation
    $evaluation = $manager->getEvaluation($evaluation_id);
    if (!$evaluation) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Evaluation not found']);
        exit;
    }

    // Facility access control: check if user can access this evaluation's facility
    // User must be creator, editor, or holder of the facility
    $user_role = $_SESSION['role'] ?? 'viewer';
    if ($user_role !== 'admin' && in_array($user_role, ['viewer', 'input'])) {
        $user_id = $_SESSION['user_id'] ?? null;
        $user_facility = $_SESSION['facility_name'] ?? null;
        if (!$facility_manager->canUserAccessFacility($evaluation['facility_id'], $user_id, $user_facility)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied: evaluation is not in an accessible facility']);
            exit;
        }
    }

    // Get scores organized by building blocks
    $scores = $manager->getScoresForEvaluation($evaluation_id);
    
    // Organize scores by building block and strategic objective
    $organized_data = [];
    $current_bb = null;
    $current_so = null;

    foreach ($scores as $score) {
        $bb_name = $score['building_block_name'];
        
        if (!isset($organized_data[$bb_name])) {
            $organized_data[$bb_name] = [
                'weight' => $score['weight_percentage'],
                'objectives' => []
            ];
        }

        $so_name = $score['objective_name'];
        
        if (!isset($organized_data[$bb_name]['objectives'][$so_name])) {
            $organized_data[$bb_name]['objectives'][$so_name] = [];
        }

        $organized_data[$bb_name]['objectives'][$so_name][] = [
            'id' => $score['id'],
            'kpi_id' => $score['key_performance_indicator_id'],
            'indicator_name' => $score['indicator_name'],
            'target_value' => $score['target_value'],
            'score_value' => $score['score_value'],
            'percentage_value' => $score['percentage_value'],
            'remarks' => $score['remarks'],
            'data_type' => $score['data_type']
        ];
    }

    // Calculate totals
    $totals = $manager->calculateTotals($evaluation_id);

    $response = [
        'success' => true,
        'evaluation' => $evaluation,
        'data' => $organized_data,
        'totals' => $totals
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Get Evaluation Data API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>
