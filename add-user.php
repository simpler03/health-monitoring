<?php
/**
 * Add User Page
 */

require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
Auth::requireRole('admin');

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/FacilityManager.php';

$message = '';
$message_type = '';
$auth = null;

try {
    $database = new Database();
    $db = $database->connect();
    $auth = new Auth($db);
    $facility_manager = new FacilityManager($db);
    $facilities = $facility_manager->getAllFacilitiesForAdmin();
} catch (Exception $e) {
    $facilities = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!$auth) {
            $database = new Database();
            $db = $database->connect();
            $auth = new Auth($db);
        }
        
        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'full_name' => trim($_POST['full_name'] ?? ''),
            'role' => $_POST['role'] ?? 'viewer',
            'facility_name' => trim($_POST['facility_name'] ?? '') ?: null
        ];
        
        if (strlen($data['password']) < 6) {
            $message = 'Password must be at least 6 characters.';
            $message_type = 'error';
        } else {
            $result = $auth->register($data);
            
            if ($result['success']) {
                // Log the creation
                $new_user_id = $db->lastInsertId();
                $new_value = "username: {$data['username']}\nemail: {$data['email']}\nfull_name: {$data['full_name']}\nrole: {$data['role']}\nfacility_name: " . ($data['facility_name'] ?? 'null');
                $auth->logAuditTrail($_SESSION['user_id'], 'CREATE_USER', 'USER', $new_user_id, null, $new_value);
                
                header("Location: users.php?success=1");
                exit;
            }
            $message = $result['message'] ?? 'Failed to create user';
            $message_type = 'error';
        }
    } catch (Exception $e) {
        error_log("Add User Error: " . $e->getMessage());
        $message = 'An error occurred. Please try again.';
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Health Performance Monitoring System</title>
    <link rel="stylesheet" href="<?php echo asset('css/styles.css'); ?>">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h1 { margin: 0 0 24px 0; color: #333; font-size: 24px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 2px rgba(102,126,234,0.2); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .btn { padding: 10px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; }
        .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .btn-secondary { background: #e0e0e0; color: #333; text-decoration: none; display: inline-block; }
        .btn-group { display: flex; gap: 12px; margin-top: 24px; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; }
        .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .back-link { color: #667eea; text-decoration: none; display: inline-block; margin-bottom: 20px; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="container">
                <a class="back-link" href="users.php">← Back to Users</a>
        <div class="card">
            <h1>Add User</h1>
            <?php if ($message): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required minlength="6" placeholder="At least 6 characters">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select id="role" name="role" required>
                            <option value="viewer" <?php echo ($_POST['role'] ?? '') === 'viewer' ? 'selected' : ''; ?>>Viewer</option>
                            <option value="input" <?php echo ($_POST['role'] ?? '') === 'input' ? 'selected' : ''; ?>>Input</option>
                            <option value="admin" <?php echo ($_POST['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="facility_name">Facility</label>
                        <select id="facility_name" name="facility_name">
                            <option value="">— Select Facility —</option>
                            <?php foreach ($facilities as $f): ?>
                                <option value="<?php echo htmlspecialchars($f['name']); ?>" <?php echo ($_POST['facility_name'] ?? '') === $f['name'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($f['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Create User</button>
                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
            </div>
        </div>
    </div>
</body>
</html>
