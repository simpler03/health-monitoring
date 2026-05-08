<?php
/**
 * AJAX API - Update evaluation status (Save Draft / Submit)
 * Health Performance Monitoring System
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once dirname(__DIR__) . '/config/BaseConfig.php';
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/config/Auth.php';
require_once dirname(__DIR__) . '/config/EvaluationManager.php';
require_once dirname(__DIR__) . '/config/FacilityManager.php';

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    if (empty($input['evaluation_id']) || empty($input['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing evaluation_id or status']);
        exit;
    }

    $evaluation_id = (int)$input['evaluation_id'];
    $status = trim($input['status']);

    $allowed = ['draft', 'in_progress', 'completed', 'approved'];
    if (!in_array($status, $allowed)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }

    $user_role = $_SESSION['role'] ?? 'viewer';
    if ($user_role === 'viewer') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        exit;
    }

    $database = new Database();
    $db = $database->connect();
    $manager = new EvaluationManager($db);
    $facility_manager = new FacilityManager($db);

    $evaluation = $manager->getEvaluation($evaluation_id);
    if (!$evaluation) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Evaluation not found']);
        exit;
    }

    // Facility access control: check if user can access this evaluation's facility
    // User must be creator, editor, or holder of the facility
    // Admins have full control and bypass this check
    if ($user_role !== 'admin' && $user_role === 'input') {
        $user_id = $_SESSION['user_id'] ?? null;
        $user_facility = $_SESSION['facility_name'] ?? null;
        if (!$facility_manager->canUserAccessFacility($evaluation['facility_id'], $user_id, $user_facility)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied: evaluation is not in an accessible facility']);
            exit;
        }
    }

    $result = $manager->updateStatus($evaluation_id, $status);
    echo json_encode($result);

} catch (Exception $e) {
    error_log("Update Evaluation Status Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
