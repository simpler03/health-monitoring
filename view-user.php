<?php
/**
 * View User Page
 */

require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
Auth::requireRole('admin');

require_once __DIR__ . '/config/Database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header("Location: users.php");
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();
    $auth = new Auth($db);
    $view_user = $auth->getUserById($id);
    $is_online = $auth->isUserOnline($id);
} catch (Exception $e) {
    $view_user = null;
    $is_online = false;
}

if (!$view_user) {
    header("Location: users.php");
    exit;
}
$success_message = isset($_GET['success']) ? 'User updated successfully.' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User - Health Performance Monitoring System</title>
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
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-admin { background: #fce4ec; color: #880e4f; }
        .badge-input { background: #e3f2fd; color: #1565c0; }
        .badge-viewer { background: #f1f8e9; color: #33691e; }
        .status-online { color: #2e7d32; }
        .status-offline { color: #c62828; }
        .btn-group { display: flex; gap: 12px; margin-top: 24px; }
        .btn { padding: 10px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .back-link { color: #667eea; text-decoration: none; display: inline-block; margin-bottom: 20px; font-weight: 500; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="container">
                <a class="back-link" href="users.php">← Back to Users</a>
        <?php if ($success_message): ?>
            <div style="background:#e8f5e9;color:#2e7d32;padding:12px 16px;border-radius:4px;margin-bottom:20px;"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <div class="card">
            <h1><?php echo htmlspecialchars($view_user['full_name']); ?></h1>
            <div class="detail-row">
                <span class="detail-label">Username</span>
                <span class="detail-value"><?php echo htmlspecialchars($view_user['username']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value"><?php echo htmlspecialchars($view_user['email']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Role</span>
                <span class="detail-value"><span class="badge badge-<?php echo $view_user['role']; ?>"><?php echo ucfirst($view_user['role']); ?></span></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Facilities</span>
                <span class="detail-value"><?php echo htmlspecialchars($auth->formatUserFacilities($id, $view_user['facility_name'] ?? null)); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value status-<?php echo $is_online ? 'online' : 'offline'; ?>"><?php echo $is_online ? 'Online' : 'Offline'; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created</span>
                <span class="detail-value"><?php echo date('M j, Y', strtotime($view_user['created_at'] ?? 'now')); ?></span>
            </div>
            <div class="btn-group">
                <a href="edit-user.php?id=<?php echo $id; ?>" class="btn btn-primary">Edit User</a>
                <a href="users.php" class="btn btn-secondary">Back to List</a>
            </div>
            </div>
        </div>
    </div>
</body>
</html>
