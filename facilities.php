<?php
/**
 * Facilities Management Page
 */

require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
Auth::requireLogin();

$user = $_SESSION;
$current_role = $user['role'] ?? 'viewer';
// Input and admin can edit/add facilities
$can_edit = in_array($current_role, ['admin', 'input']);

require_once 'config/Database.php';
require_once 'config/FacilityManager.php';

try {
    $database = new Database();
    $db = $database->connect();
    $facility_manager = new FacilityManager($db);
    
    if ($current_role === 'admin') {
        $facilities = $facility_manager->getAllFacilitiesForAdmin();
    } else {
        // For input and viewer, show facilities they created or edited, or match their facility_name
        $facilities = $facility_manager->getFacilitiesForUser($user['user_id'], $user['facility_name'] ?? null);
    }
} catch (Exception $e) {
    $facilities = [];
}

$success_message = isset($_GET['success']) ? 'Facility created successfully.' : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facilities - Health Performance Monitoring System</title>
    <link rel="stylesheet" href="<?php echo asset('css/styles.css'); ?>">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { background: white; padding: 25px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .header h1 { margin: 0; color: #333; }
        .btn { padding: 10px 20px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 4px; cursor: pointer; }
        .section { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f5f5f5; padding: 12px; text-align: left; font-weight: 600; border-bottom: 2px solid #ddd; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .back-link { color: #667eea; text-decoration: none; display: inline-block; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="container">
                <a class="back-link" href="dashboard.php">← Back</a>
        <div class="header">
            <h1>Facilities</h1>
            <?php if ($can_edit): ?>
                <a href="add-facility.php" class="btn" style="text-decoration:none;color:white;display:inline-block;">+ Add Facility</a>
            <?php endif; ?>
        </div>
        <?php if ($success_message): ?>
            <div style="background:#e8f5e9;color:#2e7d32;padding:12px 16px;border-radius:4px;margin-bottom:20px;"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <div class="section">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Province</th>
                        <th>Type</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <?php if (!in_array($current_role, ['admin'])): ?><th>Purpose</th><?php endif; ?>
                        <th style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($facilities as $facility): ?>
                        <?php 
                            $relationships = [];
                            if (!in_array($current_role, ['admin'])) {
                                $relationships = $facility_manager->getUserFacilityRelationships($facility['id'], $user['user_id'], $user['facility_name'] ?? null);
                            }
                        ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 20px; height: 20px; background: <?php echo htmlspecialchars($facility['color_legend'] ?? '#2e7d32'); ?>; border-radius: 3px; border: 1px solid #ddd;"></div>
                                    <span><?php echo htmlspecialchars($facility['name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($facility['province'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($facility['facility_type'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($facility['contact_email'] ?? '-'); ?></td>
                            <td><?php echo $facility['is_active'] ? 'Active' : 'Inactive'; ?></td>
                            <?php if (!in_array($current_role, ['admin'])): ?>
                            <td>
                                <?php foreach ($relationships as $rel): ?>
                                    <span style="display:inline-block;padding:4px 8px;border-radius:3px;font-size:11px;font-weight:600;margin-right:4px;color:white;<?php 
                                        if ($rel === 'owned') echo 'background:#4caf50;';
                                        elseif ($rel === 'edited') echo 'background:#2196F3;';
                                        elseif ($rel === 'holder') echo 'background:#FF9800;';
                                    ?>">
                                        <?php echo ucfirst($rel); ?>
                                    </span>
                                <?php endforeach; ?>
                            </td>
                            <?php endif; ?>
                            <td>
                                <a href="view-facility.php?id=<?php echo (int)$facility['id']; ?>" style="color:#667eea;text-decoration:none;margin-right:8px;">View</a>
                                <?php if ($can_edit): ?>
                                    <a href="edit-facility.php?id=<?php echo (int)$facility['id']; ?>" style="color:#667eea;text-decoration:none;">Edit</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</body>
</html>
