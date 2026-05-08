<?php
/**
 * Edit User Page
 */

require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
Auth::requireRole('admin');

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/FacilityManager.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header("Location: users.php");
    exit;
}

$message = '';
$message_type = '';

try {
    $database = new Database();
    $db = $database->connect();
    $auth = new Auth($db);
    $facility_manager = new FacilityManager($db);
    $user = $auth->getUserById($id);
    $facilities = $facility_manager->getAllFacilitiesForAdmin();
} catch (Exception $e) {
    $user = null;
    $facilities = [];
}

if (!$user) {
    header("Location: users.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'role' => $_POST['role'] ?? 'viewer',
        'facility_name' => trim($_POST['facility_name'] ?? '') ?: null,
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'password' => $_POST['password'] ?? ''
    ];
    if (!empty($data['password']) && strlen($data['password']) < 6) {
        $message = 'Password must be at least 6 characters.';
    } else {
        $result = $auth->updateUser($id, $data);
        if ($result['success']) {
            // Log the changes
            $changes = [];
            if ($user['username'] !== $data['username']) $changes[] = "username: {$user['username']} -> {$data['username']}";
            if ($user['email'] !== $data['email']) $changes[] = "email: {$user['email']} -> {$data['email']}";
            if ($user['full_name'] !== $data['full_name']) $changes[] = "full_name: {$user['full_name']} -> {$data['full_name']}";
            if ($user['role'] !== $data['role']) $changes[] = "role: {$user['role']} -> {$data['role']}";
            if (($user['facility_name'] ?? '') !== ($data['facility_name'] ?? '')) $changes[] = "facility_name: " . ($user['facility_name'] ?? 'null') . " -> " . ($data['facility_name'] ?? 'null');
            if ($user['is_active'] != $data['is_active']) $changes[] = "is_active: {$user['is_active']} -> {$data['is_active']}";
            if (!empty($data['password'])) $changes[] = "password: changed";
            
            if (!empty($changes)) {
                $auth->logAuditTrail($_SESSION['user_id'], 'UPDATE_USER', 'USER', $id, null, implode("\n", $changes));
            }
            
            header("Location: view-user.php?id=" . $id . "&success=1");
            exit;
        }
        $message = $result['message'] ?? 'Update failed';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Health Performance Monitoring System</title>
    <link rel="stylesheet" href="<?php echo asset('css/styles.css'); ?>">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; padding: 20px; margin: 0; }
        .container { max-width: 700px; margin: 0 auto; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .card h1 { margin: 0 0 30px 0; color: #1a1a1a; font-size: 28px; font-weight: 600; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 14px; }
        .form-group label .required { color: #e74c3c; }
        .form-group input,
        .form-group select,
        .form-group textarea { width: 100%; padding: 12px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; font-family: inherit; transition: all 0.2s ease; }
        .form-group input:hover,
        .form-group select:hover,
        .form-group textarea:hover { border-color: #bbb; }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .form-group input:disabled { background-color: #f5f5f5; color: #999; cursor: not-allowed; }
        .form-group input[type="checkbox"] { width: auto; margin-right: 8px; cursor: pointer; }
        .form-group .hint { font-size: 12px; color: #999; margin-top: 4px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
        .form-help { font-size: 12px; color: #999; margin-top: 4px; }
        .btn { padding: 12px 28px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-block; transition: all 0.2s ease; }
        .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4); }
        .btn-primary:active { transform: translateY(0); }
        .btn-secondary { background: #e8e8e8; color: #333; border: 1px solid #ddd; }
        .btn-secondary:hover { background: #d8d8d8; }
        .btn-group { display: flex; gap: 12px; margin-top: 30px; }
        .alert-error { background: #ffebee; color: #c62828; padding: 14px 16px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #c62828; font-size: 14px; }
        .alert-success { background: #e8f5e9; color: #2e7d32; padding: 14px 16px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #2e7d32; font-size: 14px; }
        .back-link { color: #667eea; text-decoration: none; display: inline-block; margin-bottom: 20px; font-weight: 500; font-size: 14px; transition: color 0.2s ease; }
        .back-link:hover { color: #764ba2; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="container">
                <a class="back-link" href="view-user.php?id=<?php echo $id; ?>">← Back to User</a>
        <div class="card">
            <h1>Edit User</h1>
            <?php if ($message): ?><div class="alert-error"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($user['username']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" minlength="6" placeholder="Leave blank to keep current">
                    <div class="hint">Leave blank to keep current password. Min 6 characters if changing.</div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select id="role" name="role" required>
                            <option value="viewer" <?php echo $user['role'] === 'viewer' ? 'selected' : ''; ?>>Viewer</option>
                            <option value="input" <?php echo $user['role'] === 'input' ? 'selected' : ''; ?>>Input</option>
                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="facility_name">Facility</label>
                        <select id="facility_name" name="facility_name">
                            <option value="">— Select Facility —</option>
                            <?php foreach ($facilities as $f): ?>
                                <option value="<?php echo htmlspecialchars($f['name']); ?>" <?php echo ($user['facility_name'] ?? '') === $f['name'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($f['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="is_active" value="1" <?php echo $user['is_active'] ? 'checked' : ''; ?>> Active</label>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="view-user.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
            </div>
        </div>
    </div>
</body>
</html>
