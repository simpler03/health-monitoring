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
$editing_user_id = $id;

try {
    $database = new Database();
    $db = $database->connect();
    $auth = new Auth($db);
    $facility_manager = new FacilityManager($db);
    $user = $auth->getUserById($id);
    $original_user = $user;
    $facilities = $facility_manager->getAllFacilitiesForAdmin();
} catch (Exception $e) {
    $user = null;
    $original_user = null;
    $facilities = [];
}

if (!$user) {
    header("Location: users.php");
    exit;
}

$checked_facility_ids = array_map('intval', $user['facility_ids'] ?? []);
$legacy_facility_missing = '';
if (!empty($user['facility_name'])) {
    $legacy_facility_found = false;
    foreach ($facilities as $facility) {
        if ($facility['name'] === $user['facility_name']) {
            $legacy_facility_found = true;
            $checked_facility_ids[] = (int)$facility['id'];
            break;
        }
    }
    if (!$legacy_facility_found) {
        $legacy_facility_missing = $user['facility_name'];
    }
}
$checked_facility_ids = array_values(array_unique($checked_facility_ids));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    if ($posted_user_id !== $editing_user_id) {
        $message = 'This edit form is out of date. Please reopen the user and try again.';
        $message_type = 'error';
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
        'username' => $original_user['username'],
        'email' => $original_user['email'],
        'full_name' => $original_user['full_name'],
        'role' => $original_user['role'],
        'facility_name' => $primary_facility_name,
        'facility_ids' => $selected_facility_ids,
        'is_active' => $original_user['is_active'],
        'password' => $_POST['password'] ?? ''
    ];
    if (!$message && !empty($data['password']) && strlen($data['password']) < 6) {
        $message = 'Password must be at least 6 characters.';
        $message_type = 'error';
    } elseif (!$message) {
        $result = $auth->updateUser($id, $data);
        if ($result['success']) {
            // Log the changes
            $changes = [];
            if ($user['username'] !== $data['username']) $changes[] = "username: {$user['username']} -> {$data['username']}";
            if ($user['email'] !== $data['email']) $changes[] = "email: {$user['email']} -> {$data['email']}";
            if ($user['full_name'] !== $data['full_name']) $changes[] = "full_name: {$user['full_name']} -> {$data['full_name']}";
            if ($user['role'] !== $data['role']) $changes[] = "role: {$user['role']} -> {$data['role']}";
            $old_facilities = $user['facility_names'] ?? [];
            $new_facilities = array_values(array_map(fn($facility) => $facility['name'], array_filter($facilities, fn($facility) => in_array((int)$facility['id'], $selected_facility_ids, true))));
            if ($old_facilities !== $new_facilities) $changes[] = "facilities: " . (!empty($old_facilities) ? implode(', ', $old_facilities) : 'none') . " -> " . (!empty($new_facilities) ? implode(', ', $new_facilities) : 'none');
            if ($user['is_active'] != $data['is_active']) $changes[] = "is_active: {$user['is_active']} -> {$data['is_active']}";
            if (!empty($data['password'])) $changes[] = "password: changed";
            
            if (!empty($changes)) {
                $auth->logAuditTrail($_SESSION['user_id'], 'UPDATE_USER', 'USER', $id, null, implode("\n", $changes));
            }

            if ((int)($_SESSION['user_id'] ?? 0) === (int)$id) {
                $_SESSION['facility_names'] = $new_facilities;
                $_SESSION['facility_name'] = $new_facilities[0] ?? null;
            }
            
            header("Location: view-user.php?id=" . $id . "&success=1");
            exit;
        }
        $message = $result['message'] ?? 'Update failed';
        $message_type = 'error';
    }

    $user['username'] = $data['username'];
    $user['email'] = $data['email'];
    $user['full_name'] = $data['full_name'];
    $user['role'] = $data['role'];
    $user['facility_ids'] = $selected_facility_ids;
    $user['facility_names'] = array_values(array_map(fn($facility) => $facility['name'], array_filter($facilities, fn($facility) => in_array((int)$facility['id'], $selected_facility_ids, true))));
    $user['is_active'] = $data['is_active'];
    $checked_facility_ids = $selected_facility_ids;
    $legacy_facility_missing = '';
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
        .facility-checkbox-list { width: 100%; max-height: 160px; overflow-y: auto; border: 1px solid #ddd; border-radius: 6px; padding: 10px 12px; background: white; }
        .facility-checkbox { display: flex; align-items: flex-start; gap: 8px; padding: 6px 0; font-weight: 400 !important; line-height: 1.35; cursor: pointer; }
        .facility-checkbox input { width: auto; margin: 2px 0 0 0; flex: 0 0 auto; }
        .facility-checkbox span { flex: 1; }
        .facility-checkbox:has(input:checked) { color: #2e7d32; font-weight: 600 !important; }
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
            <div class="hint" style="margin-top:-20px;margin-bottom:20px;">Editing user #<?php echo (int)$editing_user_id; ?>: <?php echo htmlspecialchars($original_user['username'] ?? $user['username']); ?></div>
            <?php if ($message): ?><div class="alert-error"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <form method="POST">
                <input type="hidden" name="user_id" value="<?php echo (int)$editing_user_id; ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required readonly value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required readonly value="<?php echo htmlspecialchars($user['username']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required readonly value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" minlength="6" placeholder="Leave blank to keep current">
                    <div class="hint">Leave blank to keep current password. Min 6 characters if changing.</div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select id="role" name="role" required disabled>
                            <option value="viewer" <?php echo $user['role'] === 'viewer' ? 'selected' : ''; ?>>Viewer</option>
                            <option value="input" <?php echo $user['role'] === 'input' ? 'selected' : ''; ?>>Input</option>
                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Facilities</label>
                        <div class="facility-checkbox-list">
                            <?php foreach ($facilities as $f): ?>
                                <label class="facility-checkbox">
                                    <input type="checkbox" name="facility_ids[]" value="<?php echo (int)$f['id']; ?>" <?php echo in_array((int)$f['id'], $checked_facility_ids, true) ? 'checked' : ''; ?>>
                                    <span><?php echo htmlspecialchars($f['name']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-help">Check every facility this user can access.</div>
                        <?php if ($legacy_facility_missing): ?>
                            <div class="form-help" style="color:#c62828;">Current designated facility is not in the facilities list: <?php echo htmlspecialchars($legacy_facility_missing); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="is_active" value="1" <?php echo $user['is_active'] ? 'checked' : ''; ?> disabled> Active</label>
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
