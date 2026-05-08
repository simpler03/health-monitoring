<?php
/**
 * Edit Facility Page
 */

require_once __DIR__ . '/config/BaseConfig.php';
require_once 'config/Auth.php';
Auth::requireLogin();

$user = $_SESSION;
$current_role = $user['role'] ?? 'viewer';

// Facility access control: only admins and input can edit facilities
if (!in_array($current_role, ['admin', 'input'])) {
    header("Location: unauthorized.php");
    exit;
}

require_once 'config/Database.php';
require_once 'config/FacilityManager.php';

function format_field_name($key) {
    $names = [
        'name' => 'facility name',
        'facility_type' => 'type',
        'province' => 'province',
        'municipality' => 'municipality',
        'address' => 'address',
        'contact_person' => 'contact person',
        'contact_email' => 'contact email',
        'contact_phone' => 'contact phone',
        'color_legend' => 'facility color'
    ];
    return $names[$key] ?? $key;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header("Location: facilities.php");
    exit;
}

$message = '';
$message_type = '';

try {
    $database = new Database();
    $db = $database->connect();
    $manager = new FacilityManager($db);
    $facility = $manager->getFacilityById($id);
} catch (Exception $e) {
    $facility = null;
}

if (!$facility) {
    header("Location: facilities.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'facility_type' => trim($_POST['facility_type'] ?? '') ?: null,
        'province' => trim($_POST['province'] ?? '') ?: null,
        'municipality' => trim($_POST['municipality'] ?? '') ?: null,
        'address' => trim($_POST['address'] ?? '') ?: null,
        'contact_person' => trim($_POST['contact_person'] ?? '') ?: null,
        'contact_email' => trim($_POST['contact_email'] ?? '') ?: null,
        'contact_phone' => trim($_POST['contact_phone'] ?? '') ?: null,
        'color_legend' => trim($_POST['color_legend'] ?? '') ?: null,
        'editor' => $_SESSION['user_id'] ?? null
    ];
    $result = $manager->updateFacility($id, $data);
    if ($result['success']) {
        // Add audit log for facility update
        $changes = [];
        $fields = ['name', 'facility_type', 'province', 'municipality', 'address', 'contact_person', 'contact_email', 'contact_phone', 'color_legend'];
        foreach ($fields as $field) {
            $old_val = $facility[$field] ?? '';
            $new_val = $data[$field] ?? '';
            if ($old_val !== $new_val) {
                $changes[] = format_field_name($field) . ": " . htmlspecialchars($old_val) . " -> " . htmlspecialchars($new_val);
            }
        }
        if (!empty($changes)) {
            $change_string = implode("\n", $changes);
            $audit_query = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_value, new_value, ip_address, user_agent) VALUES (?, 'UPDATE_FACILITY', 'USER', ?, NULL, ?, ?, ?)";
            $stmt = $db->prepare($audit_query);
            $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $change_string, $_SERVER['REMOTE_ADDR'] ?? '::1', $_SERVER['HTTP_USER_AGENT'] ?? '']);
        }
        header("Location: view-facility.php?id=" . $id . "&success=1");
        exit;
    }
    $message = $result['message'] ?? 'Update failed';
    $message_type = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Facility - Health Performance Monitoring System</title>
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
                <a class="back-link" href="view-facility.php?id=<?php echo $id; ?>">← Back to Facility</a>
        <div class="card">
            <h1>Edit Facility</h1>
            <?php if ($message): ?><div class="alert-error"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="name">Facility Name *</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($facility['name']); ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="facility_type">Type</label>
                        <input type="text" id="facility_type" name="facility_type" value="<?php echo htmlspecialchars($facility['facility_type'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="province">Province</label>
                        <input type="text" id="province" name="province" value="<?php echo htmlspecialchars($facility['province'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="municipality">Municipality/City</label>
                    <input type="text" id="municipality" name="municipality" value="<?php echo htmlspecialchars($facility['municipality'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($facility['address'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="color_legend">Facility Color</label>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <input type="color" id="color_legend_picker" name="color_legend_display" value="<?php echo htmlspecialchars($facility['color_legend'] ?? '#2e7d32'); ?>" style="width: 60px; height: 45px; cursor: pointer; border: 1px solid #ddd; border-radius: 6px;">
                        <input type="text" id="color_legend" name="color_legend" value="<?php echo htmlspecialchars($facility['color_legend'] ?? '#2e7d32'); ?>" placeholder="#2e7d32" style="flex: 1;" pattern="^#[0-9A-Fa-f]{6}$">
                    </div>
                    <div class="form-help">Use hex color code (e.g., #2e7d32). Color picker updates the text field.</div>
                </div>
                <script>
                    // Sync color picker with text input
                    const colorPicker = document.getElementById('color_legend_picker');
                    const colorText = document.getElementById('color_legend');
                    colorPicker.addEventListener('input', function() {
                        colorText.value = this.value;
                    });
                    colorText.addEventListener('input', function() {
                        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                            colorPicker.value = this.value;
                        }
                    });
                </script>
                <div class="form-group">
                    <label for="contact_person">Contact Person</label>
                    <input type="text" id="contact_person" name="contact_person" value="<?php echo htmlspecialchars($facility['contact_person'] ?? ''); ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_email">Contact Email</label>
                        <input type="email" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($facility['contact_email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="contact_phone">Contact Phone</label>
                        <input type="text" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($facility['contact_phone'] ?? ''); ?>">
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="view-facility.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
            </div>
        </div>
    </div>
</body>
</html>
