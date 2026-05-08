<?php
require_once __DIR__ . '/../config/Auth.php';
Auth::requireLogin();

header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/EvaluationManager.php';
require_once __DIR__ . '/../config/FacilityManager.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (empty($data) || empty($data['evaluation_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid payload']);
    exit;
}

$evaluation_id = (int)$data['evaluation_id'];

try {
    $database = new Database();
    $db = $database->connect();
    $manager = new EvaluationManager($db);
    $facility_manager = new FacilityManager($db);

    // Check if evaluation is already completed
    $evaluation = $manager->getEvaluation($evaluation_id);
    if (!$evaluation) {
        echo json_encode(['success' => false, 'message' => 'Evaluation not found']);
        exit;
    }
    if ($evaluation['status'] === 'completed') {
        echo json_encode(['success' => false, 'message' => 'Evaluation is already completed and cannot be edited']);
        exit;
    }

    // Facility access control and permission check
    $user_role = $_SESSION['role'] ?? 'viewer';
    if ($user_role === 'viewer') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        exit;
    }
    
    // For input users: check if they can access this evaluation's facility
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

    // Prepare signature data: only include known fields
    $allowed = [
        'assessed_by','assessed_signature','assessed_date','assessed_esignature',
        'verified_by','verified_signature','verified_date','verified_esignature',
        'approved_by','approved_signature','approved_date','approved_esignature',
        'noted_by','noted_signature','noted_date','noted_esignature',
        'conformed_by','conformed_signature','conformed_date','conformed_esignature'
    ];

    $sig = [];
    foreach ($allowed as $k) {
        if (array_key_exists($k, $data)) {
            $value = $data[$k];
            // Handle esignature base64 data
            if (strpos($k, '_esignature') !== false && $value && strpos($value, 'data:image/png;base64,') === 0) {
                // Decode base64 to binary
                $base64 = str_replace('data:image/png;base64,', '', $value);
                $sig[$k] = base64_decode($base64);
            } else {
                $sig[$k] = $value;
            }
        }
    }

    if (empty($sig)) {
        echo json_encode(['success' => false, 'message' => 'No signature fields provided']);
        exit;
    }

    // Get old signature data before updating
    $old_evaluation = $manager->getEvaluation($evaluation_id);
    $facility_query = "SELECT name FROM facilities WHERE id = ?";
    $facility_stmt = $db->prepare($facility_query);
    $facility_stmt->execute([$old_evaluation['facility_id']]);
    $facility = $facility_stmt->fetch();
    $facility_name = $facility ? htmlspecialchars($facility['name']) : 'Unknown Facility';
    
    $changes = [];

    // Track changes for audit logging
    $signature_fields = [
        'assessed' => 'Assessed',
        'verified' => 'Verified', 
        'approved' => 'Approved',
        'noted' => 'Noted',
        'conformed' => 'Conformed'
    ];

    foreach ($signature_fields as $prefix => $label) {
        // Check name changes
        $name_field = $prefix . '_by';
        if (array_key_exists($name_field, $data)) {
            $old_name = $old_evaluation[$name_field] ?? '';
            $new_name = $data[$name_field] ?? '';
            if ($old_name !== $new_name) {
                $old_display = !empty($old_name) ? $old_name : '(empty)';
                $new_display = !empty($new_name) ? $new_name : '(empty)';
                $changes[] = $label . " signature name: " . $old_display . " -> " . $new_display;
            }
        }

        // Check date changes
        $date_field = $prefix . '_date';
        if (array_key_exists($date_field, $data)) {
            $old_date = $old_evaluation[$date_field] ?? '';
            $new_date = $data[$date_field] ?? '';
            if ($old_date !== $new_date) {
                $old_display = !empty($old_date) ? $old_date : '(empty)';
                $new_display = !empty($new_date) ? $new_date : '(empty)';
                $changes[] = $label . " date: " . $old_display . " -> " . $new_display;
            }
        }

        // Check e-signature changes
        $esig_field = $prefix . '_esignature';
        if (array_key_exists($esig_field, $data)) {
            $old_esig = $old_evaluation[$esig_field] ?? null;
            $new_esig = $data[$esig_field] ?? null;
            $old_has = !empty($old_esig);
            $new_has = !empty($new_esig);
            
            if ($old_has !== $new_has) {
                if ($new_has && !$old_has) {
                    $changes[] = $label . " e-signature: added";
                } elseif (!$new_has && $old_has) {
                    $changes[] = $label . " e-signature: removed";
                }
            } elseif ($old_has && $new_has && $old_esig !== $new_esig) {
                $changes[] = $label . " e-signature: updated";
            }
        }
    }

    $result = $manager->updateSignatures($evaluation_id, $sig);
    
    // Log to audit_logs if successfully saved and there are changes
    if ($result['success'] && !empty($changes)) {
        try {
            $change_string = "facility: " . $facility_name . "\n" . implode("\n", $changes);
            $audit_query = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_value, new_value, ip_address, user_agent) 
                           VALUES (?, 'UPDATE_EVALUATION', 'USER', ?, NULL, ?, ?, ?)";
            $audit_stmt = $db->prepare($audit_query);
            $audit_stmt->execute([
                $_SESSION['user_id'],
                $_SESSION['user_id'],
                $change_string,
                $_SERVER['REMOTE_ADDR'] ?? '::1',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (Exception $audit_error) {
            error_log("Audit log error in save_signatures: " . $audit_error->getMessage());
            // Don't fail the signature save if audit logging fails
        }
    }
    
    echo json_encode($result);
    exit;

} catch (Exception $e) {
    error_log('Save Signatures Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}

?>
