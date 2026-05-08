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
require_once __DIR__ . '/../config/FacilityManager.php';

function send_json_response($payload, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($payload);
    exit;
}

function ensure_approval_column(PDO $db) {
    $stmt = $db->prepare("SHOW COLUMNS FROM evaluations LIKE 'approved'");
    $stmt->execute();

    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        $db->exec("ALTER TABLE evaluations ADD COLUMN approved TINYINT(4) NOT NULL DEFAULT 0");
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    send_json_response(['success' => false, 'message' => 'Unauthorized'], 401);
}

$current_user_id = $_SESSION['user_id'];
$current_role = $_SESSION['role'] ?? '';

// Only viewers can approve evaluations
if ($current_role !== 'viewer') {
    send_json_response(['success' => false, 'message' => 'Only viewers can approve or reject evaluations'], 403);
}

// Get JSON payload
$payload = json_decode(file_get_contents('php://input'), true);

if (!isset($payload['evaluation_id']) || !isset($payload['approved'])) {
    send_json_response(['success' => false, 'message' => 'Missing required parameters'], 400);
}

$evaluation_id = (int)$payload['evaluation_id'];
$approved_status = (int)$payload['approved'];

// Validate approved status
if (!in_array($approved_status, [0, 1, 2])) {
    send_json_response(['success' => false, 'message' => 'Invalid approval status'], 400);
}

try {
    $database = new Database();
    $db = $database->connect();
    ensure_approval_column($db);

    // Get the evaluation
    $stmt = $db->prepare('SELECT e.*, f.name AS facility_name FROM evaluations e JOIN facilities f ON e.facility_id = f.id WHERE e.id = ?');
    $stmt->execute([$evaluation_id]);
    $evaluation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$evaluation) {
        send_json_response(['success' => false, 'message' => 'Evaluation not found'], 404);
    }

    // Check that evaluation is completed
    if ($evaluation['status'] !== 'completed') {
        send_json_response(['success' => false, 'message' => 'Only completed evaluations can be approved or rejected'], 400);
    }

    $facility_manager = new FacilityManager($db);
    $user_facility = $_SESSION['facility_names'] ?? ($_SESSION['facility_name'] ?? null);
    if (!$facility_manager->canUserAccessFacility($evaluation['facility_id'], $current_user_id, $user_facility)) {
        send_json_response(['success' => false, 'message' => 'Access denied: evaluation is not in your assigned facility'], 403);
    }

    // Update the approved column
    $stmt = $db->prepare('UPDATE evaluations SET approved = ?, updated_by = ?, updated_at = NOW() WHERE id = ?');
    $result = $stmt->execute([$approved_status, $current_user_id, $evaluation_id]);

    if ($result) {
        // Log the approval action
        $auth = new Auth($db);
        $approved_text = $approved_status === 1 ? 'approved' : ($approved_status === 2 ? 'rejected' : 'neutral');
        $old_status = (int)($evaluation['approved'] ?? 0);
        $auth->logAuditTrail(
            $current_user_id,
            'EVALUATE_APPROVAL',
            'EVALUATION',
            $evaluation_id,
            $old_status,
            "facility: " . ($evaluation['facility_name'] ?? 'Unknown') . "\napproval: " . $old_status . " -> " . $approved_status . " (" . $approved_text . ")"
        );

        send_json_response([
            'success' => true,
            'approved' => $approved_status,
            'message' => 'Evaluation ' . $approved_text . ' successfully'
        ]);
    } else {
        send_json_response(['success' => false, 'message' => 'Failed to update approval status'], 500);
    }

} catch (Exception $e) {
    error_log("Update Evaluation Approval Error: " . $e->getMessage());
    send_json_response(['success' => false, 'message' => 'An error occurred while updating approval'], 500);
}
