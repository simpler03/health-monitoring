<?php
/**
 * Users Management Page
 */

require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
Auth::requireRole('admin');

require_once __DIR__ . '/config/Database.php';

try {
    $database = new Database();
    $db = $database->connect();
    $auth = new Auth($db);
    $query = "SELECT id, username, email, full_name, role, facility_name, is_active FROM users ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $users = [];
    $auth = null;
}

$success_message = isset($_GET['success']) ? 'User created successfully.' : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Health Performance Monitoring System</title>
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
        .badge { display: inline-block; padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-admin { background: #fce4ec; color: #880e4f; }
        .badge-input { background: #e3f2fd; color: #1565c0; }
        .badge-viewer { background: #f1f8e9; color: #33691e; }
        .status-online { color: #2e7d32; font-weight: 600; }
        .status-offline { color: #c62828; font-weight: 600; }
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
            <h1>Users</h1>
            <a href="add-user.php" class="btn" style="text-decoration:none;color:white;display:inline-block;">+ Add User</a>
        </div>
        <?php if ($success_message): ?>
            <div style="background:#e8f5e9;color:#2e7d32;padding:12px 16px;border-radius:4px;margin-bottom:20px;"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <div class="section">
            <table>
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Facility</th>
                        <th>Status</th>
                        <th style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><span class="badge badge-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                            <td><?php echo htmlspecialchars($u['facility_name'] ?? '-'); ?></td>
                            <td><?php $is_online = $auth ? $auth->isUserOnline($u['id']) : false; ?><span class="status-<?php echo $is_online ? 'online' : 'offline'; ?>"><?php echo $is_online ? 'Online' : 'Offline'; ?></span></td>
                            <td>
                                <a href="view-user.php?id=<?php echo (int)$u['id']; ?>" style="color:#667eea;text-decoration:none;margin-right:8px;">View</a>
                                <a href="edit-user.php?id=<?php echo (int)$u['id']; ?>" style="color:#667eea;text-decoration:none;">Edit</a>
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
