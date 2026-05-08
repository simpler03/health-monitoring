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
        
        $selected_facility_ids = array_values(array_unique(array_filter(array_map('intval', $_POST['facility_ids'] ?? []))));
        $primary_facility_name = null;
        if (!empty($selected_facility_ids)) {
            foreach ($facilities as $facility) {
                if ((int)$facility['id'] === $selected_facility_ids[0]) {
                    $primary_facility_name = $facility['name'];
                    break;
                }
            }
        }

        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'full_name' => trim($_POST['full_name'] ?? ''),
            'role' => $_POST['role'] ?? 'viewer',
            'facility_name' => $primary_facility_name,
            'facility_ids' => $selected_facility_ids
        ];
        
        if (strlen($data['password']) < 6) {
            $message = 'Password must be at least 6 characters.';
            $message_type = 'error';
        } else {
            $result = $auth->register($data);
            
            if ($result['success']) {
                // Log the creation
                $new_user_id = $result['user_id'] ?? $db->lastInsertId();
                $facility_names = array_values(array_map(fn($facility) => $facility['name'], array_filter($facilities, fn($facility) => in_array((int)$facility['id'], $selected_facility_ids, true))));
                $new_value = "username: {$data['username']}\nemail: {$data['email']}\nfull_name: {$data['full_name']}\nrole: {$data['role']}\nfacilities: " . (!empty($facility_names) ? implode(', ', $facility_names) : 'none');
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
        .form-help { font-size: 12px; color: #999; margin-top: 4px; }
        .facility-checkbox-list { width: 100%; max-height: 160px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; padding: 10px 12px; background: white; }
        .facility-checkbox { display: flex !important; align-items: flex-start; gap: 8px; padding: 6px 0; margin: 0; font-weight: 400 !important; line-height: 1.35; cursor: pointer; }
        .facility-checkbox input { width: auto; margin: 2px 0 0 0; flex: 0 0 auto; }
        .facility-checkbox span { flex: 1; }
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
                        <label>Facilities</label>
                        <div class="facility-checkbox-list">
                            <?php $selected_ids = array_map('intval', $_POST['facility_ids'] ?? []); ?>
                            <?php foreach ($facilities as $f): ?>
                                <label class="facility-checkbox">
                                    <input type="checkbox" name="facility_ids[]" value="<?php echo (int)$f['id']; ?>" <?php echo in_array((int)$f['id'], $selected_ids, true) ? 'checked' : ''; ?>>
                                    <span><?php echo htmlspecialchars($f['name']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-help">Check every facility this user can access.</div>
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
