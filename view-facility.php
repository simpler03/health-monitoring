<?php
/**
 * View Facility Page
 */

require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
Auth::requireLogin();

$user = $_SESSION;
$current_role = $user['role'] ?? 'viewer';

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/FacilityManager.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header("Location: facilities.php");
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();
    $manager = new FacilityManager($db);
    $facility = $manager->getFacilityById($id);
    
    // Facility access control: admin can view all; viewer and input users have restricted access
    if (in_array($current_role, ['viewer', 'input'])) {
    if (!$manager->canUserAccessFacility($id, $user['user_id'], $user['facility_names'] ?? ($user['facility_name'] ?? null))) {
            header("Location: unauthorized.php");
            exit;
        }
    }
    
    $stats = $facility ? $manager->getFacilityStats($id) : [];
} catch (Exception $e) {
    $facility = null;
    $stats = [];
}

if (!$facility) {
    header("Location: facilities.php");
    exit;
}
$success_message = isset($_GET['success']) ? 'Facility updated successfully.' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Facility - Health Performance Monitoring System</title>
    <link rel="stylesheet" href="<?php echo asset('css/styles.css'); ?>">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 700px; margin: 0 auto; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h1 { margin: 0 0 24px 0; color: #333; font-size: 24px; border-bottom: 2px solid #667eea; padding-bottom: 12px; }
        .detail-row { display: grid; grid-template-columns: 160px 1fr; gap: 12px; padding: 10px 0; border-bottom: 1px solid #eee; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-weight: 600; color: #555; }
        .detail-value { color: #333; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-active { background: #e8f5e9; color: #2e7d32; }
        .status-inactive { background: #ffebee; color: #c62828; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 24px; }
        .stat-box { background: #f5f5f5; padding: 16px; border-radius: 8px; text-align: center; }
        .stat-value { font-size: 24px; font-weight: 700; color: #667eea; }
        .stat-label { font-size: 12px; color: #666; margin-top: 4px; }
        .btn-group { display: flex; gap: 12px; margin-top: 24px; }
        .btn { padding: 10px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .back-link { color: #667eea; text-decoration: none; display: inline-block; margin-bottom: 20px; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="container">
                <a class="back-link" href="facilities.php">← Back to Facilities</a>
        <?php if ($success_message): ?>
            <div style="background:#e8f5e9;color:#2e7d32;padding:12px 16px;border-radius:4px;margin-bottom:20px;"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <div class="card">
            <h1><?php echo htmlspecialchars($facility['name']); ?></h1>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value"><span class="status-badge status-<?php echo $facility['is_active'] ? 'active' : 'inactive'; ?>"><?php echo $facility['is_active'] ? 'Active' : 'Inactive'; ?></span></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Type</span>
                <span class="detail-value"><?php echo htmlspecialchars($facility['facility_type'] ?? '-'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Province</span>
                <span class="detail-value"><?php echo htmlspecialchars($facility['province'] ?? '-'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Municipality/City</span>
                <span class="detail-value"><?php echo htmlspecialchars($facility['municipality'] ?? '-'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Address</span>
                <span class="detail-value"><?php echo htmlspecialchars($facility['address'] ?? '-'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Contact Person</span>
                <span class="detail-value"><?php echo htmlspecialchars($facility['contact_person'] ?? '-'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Contact Email</span>
                <span class="detail-value"><?php echo htmlspecialchars($facility['contact_email'] ?? '-'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Contact Phone</span>
                <span class="detail-value"><?php echo htmlspecialchars($facility['contact_phone'] ?? '-'); ?></span>
            </div>
            <?php if (!empty($stats)): ?>
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-value"><?php echo $stats['total_evaluations'] ?? 0; ?></div>
                    <div class="stat-label">Total Evaluations</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?php echo $stats['completed_evaluations'] ?? 0; ?></div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?php echo $stats['approved_evaluations'] ?? 0; ?></div>
                    <div class="stat-label">Approved</div>
                </div>
            </div>
            <?php endif; ?>
            <div class="btn-group">
                <?php if ($current_role === 'admin'): ?>
                <a href="edit-facility.php?id=<?php echo $id; ?>" class="btn btn-primary">Edit Facility</a>
                <?php endif; ?>
                <a href="facilities.php" class="btn btn-secondary">Back to List</a>
            </div>
            </div>
        </div>
    </div>
</body>
</html>
