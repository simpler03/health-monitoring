<?php
/**
 * API: Update Evaluation Approval Status
 * Endpoint: api/update_evaluation_approval.php
 * Method: POST
 * 
 * Allows viewers to approve or reject completed evaluations
 * 0 = neutral (default)
 * 1 = approved
 * 2 = rejected
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/BaseConfig.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$current_user_id = $_SESSION['user_id'];
$current_role = $_SESSION['role'] ?? '';

// Only viewers can approve evaluations
if ($current_role !== 'viewer') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only viewers (viewer role) can approve evaluations']);
    exit;
}

// Get JSON payload
$payload = json_decode(file_get_contents('php://input'), true);

if (!isset($payload['evaluation_id']) || !isset($payload['approved'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$evaluation_id = (int)$payload['evaluation_id'];
$approved_status = (int)$payload['approved'];

// Validate approved status
if (!in_array($approved_status, [0, 1, 2])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid approval status']);
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    // Get the evaluation
    $stmt = $db->prepare('SELECT * FROM evaluations WHERE id = ?');
    $stmt->execute([$evaluation_id]);
    $evaluation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$evaluation) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Evaluation not found']);
        exit;
    }

    // Check that evaluation is completed
    if ($evaluation['status'] !== 'completed') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Only completed evaluations can be approved']);
        exit;
    }

    // Update the approved column
    $stmt = $db->prepare('UPDATE evaluations SET approved = ? WHERE id = ?');
    $result = $stmt->execute([$approved_status, $evaluation_id]);

    if ($result) {
        // Log the approval action
        $auth = new Auth($db);
        $approved_text = $approved_status === 1 ? 'approved' : ($approved_status === 2 ? 'rejected' : 'neutral');
        $auth->logAuditTrail(
            $current_user_id,
            'EVALUATE_APPROVAL',
            'EVALUATION',
            $evaluation_id,
            $evaluation['approved'] ?? 0,
            $approved_status
        );

        echo json_encode([
            'success' => true,
            'message' => 'Evaluation ' . $approved_text . ' successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update approval status']);
    }

} catch (Exception $e) {
    error_log("Update Evaluation Approval Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
