<?php
/**
 * API: Get Building Block Statistics with Filters
 * Filters evaluations by year, facility, and province
 * Returns compliance stats for each building block
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/BaseConfig.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/EvaluationManager.php';
require_once __DIR__ . '/../config/FacilityManager.php';

// Verify auth
Auth::requireLogin();

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $province = $input['province'] ?? null;
    $year = $input['year'] ?? 'all';
    $facility = $input['facility'] ?? 'all';
    
    if (!$province) {
        echo json_encode(['success' => false, 'error' => 'Province is required']);
        exit;
    }
    
    $database = new Database();
    $db = $database->connect();
    $evaluation_manager = new EvaluationManager($db);
    $facility_manager = new FacilityManager($db);
    
    // Get user's accessible facilities
    $user = $_SESSION;
    $current_role = $user['role'] ?? 'viewer';
    
    if ($current_role === 'admin') {
        $accessible_facilities = $facility_manager->getAllFacilitiesForAdmin();
    } else {
        $accessible_facilities = $facility_manager->getFacilitiesForUser($user['user_id'], $user['facility_names'] ?? ($user['facility_name'] ?? null));
    }
    
    // Get evaluations based on user role
    $filterOpts = [];
    if ($year !== 'all') {
        $filterOpts['year'] = $year;
    }

    if ($current_role === 'admin') {
        $all_evaluations = $evaluation_manager->getEvaluations($filterOpts);
    } else {
        $all_evaluations = $evaluation_manager->getEvaluationsForUser($user['user_id'], $user['facility_names'] ?? ($user['facility_name'] ?? null), $filterOpts);
    }
    
    // Build facility map
    $facilities_by_id = [];
    foreach ($accessible_facilities as $f) {
        $facilities_by_id[$f['id']] = $f;
    }
    
    // Filter evaluations
    $filtered_evals = [];
    foreach ($all_evaluations as $eval) {
        $eval_province = $facilities_by_id[$eval['facility_id']]['province'] ?? 'Unknown';
        
        // Filter by province
        if ($eval_province !== $province) {
            continue;
        }
        
        // Filter by year
        if ($year !== 'all') {
            $eval_year = date('Y', strtotime($eval['evaluation_date']));
            if ($eval_year !== $year) {
                continue;
            }
        }
        
        // Filter by facility
        if ($facility !== 'all' && $eval['facility_name'] !== $facility) {
            continue;
        }
        
        $filtered_evals[] = $eval;
    }
    
    // Calculate building block statistics
    $building_block_stats = [];
    
    foreach ($filtered_evals as $eval) {
        // Get indicators for this evaluation
        $indicators = $evaluation_manager->getIndicatorsForEvaluation($eval['id']);
        
        foreach ($indicators as $indicator) {
            $bb_name = $indicator['building_block_name'];
            
            if (!isset($building_block_stats[$bb_name])) {
                $building_block_stats[$bb_name] = [
                    'complied' => 0,
                    'not_complied' => 0,
                    'total' => 0
                ];
            }
            
            $building_block_stats[$bb_name]['total']++;
            if ($indicator['compliance_status'] == 1) {
                $building_block_stats[$bb_name]['complied']++;
            } else {
                $building_block_stats[$bb_name]['not_complied']++;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $building_block_stats,
        'count' => count($filtered_evals)
    ]);
    
} catch (Exception $e) {
    error_log("Filtered Building Blocks API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error processing request'
    ]);
}
?>
