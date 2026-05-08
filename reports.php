<?php
/**
 * Reports Page
 */

require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
Auth::requireLogin();

$user = $_SESSION;
$current_role = $user['role'] ?? 'viewer';
// Allow input, viewer, and admin roles to access reports
if (!in_array($current_role, ['admin', 'input', 'viewer'])) {
    header("Location: unauthorized.php");
    exit;
}

require_once 'config/Database.php';
require_once 'config/FacilityManager.php';
require_once 'config/EvaluationManager.php';

try {
    $database = new Database();
    $db = $database->connect();
    $facility_manager = new FacilityManager($db);
    $evaluation_manager = new EvaluationManager($db);
    
    // Get facilities based on user access
    if ($current_role === 'admin') {
        $facilities = $facility_manager->getAllFacilitiesForAdmin();
    } else {
        // For input and viewer, show facilities they created, edited, or are assigned to
        $facilities = $facility_manager->getFacilitiesForUser($user['user_id'], $user['facility_name'] ?? null);
    }
    
    // Get evaluations based on user access
    if ($current_role === 'admin') {
        $evaluations = $evaluation_manager->getEvaluations([]);
    } else {
        // For input and viewer, show evaluations for accessible facilities
        $evaluations = $evaluation_manager->getEvaluationsForUser($user['user_id'], $user['facility_name'] ?? null);
    }
    
    // Calculate summary statistics
    $total_facilities = count($facilities);
    $total_evaluations = count($evaluations);
    $completed_evaluations = count(array_filter($evaluations, fn($e) => $e['status'] === 'completed'));
    $draft_evaluations = count(array_filter($evaluations, fn($e) => $e['status'] === 'draft'));
    
} catch (Exception $e) {
    error_log("Reports Error: " . $e->getMessage());
    $facilities = [];
    $evaluations = [];
    $total_facilities = 0;
    $total_evaluations = 0;
    $completed_evaluations = 0;
    $draft_evaluations = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Health Performance Monitoring System</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { background: white; padding: 25px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; color: #333; }
        .reports-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .report-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: pointer; transition: box-shadow 0.3s; }
        .report-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.15); }
        .report-card h3 { color: #333; margin: 0 0 10px 0; }
        .report-card p { color: #666; font-size: 13px; margin: 0; }
        .back-link { color: #667eea; text-decoration: none; display: inline-block; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <a class="back-link" onclick="history.back()">← Back</a>
        <div class="header">
            <h1>Reports</h1>
        </div>
        <div class="reports-grid">
            <div class="report-card">
                <h3>📊 Evaluation Summary</h3>
                <p>Overview of all evaluations and key metrics</p>
            </div>
            <div class="report-card">
                <h3>🏢 Facility Performance</h3>
                <p>Performance scores by facility</p>
            </div>
            <div class="report-card">
                <h3>📈 Trends Analysis</h3>
                <p>Historical performance trends</p>
            </div>
            <div class="report-card">
                <h3>🎯 KPI Performance</h3>
                <p>Key performance indicators analysis</p>
            </div>
        </div>
    </div>
</body>
</html>
