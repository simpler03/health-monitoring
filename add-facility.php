<?php
/**
 * Add Facility Page
 */

require_once 'config/Auth.php';
Auth::requireLogin();

$user = $_SESSION;
$current_role = $user['role'] ?? 'viewer';
// Only admin and input can add facilities
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

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->connect();
        $manager = new FacilityManager($db);
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'facility_type' => trim($_POST['facility_type'] ?? '') ?: null,
            'province' => trim($_POST['province'] ?? '') ?: null,
            'municipality' => trim($_POST['municipality'] ?? '') ?: null,
            'address' => trim($_POST['address'] ?? '') ?: null,
            'contact_person' => trim($_POST['contact_person'] ?? '') ?: null,
            'contact_email' => trim($_POST['contact_email'] ?? '') ?: null,
            'contact_phone' => trim($_POST['contact_phone'] ?? '') ?: null,
            'color_legend' => trim($_POST['color_legend'] ?? '') ?: '#2e7d32',
            'creator' => $_SESSION['user_id'] ?? null
        ];
        
        $result = $manager->createFacility($data);
        
        if ($result['success']) {
            // Add audit log for facility creation
            $facility_id = $result['facility_id'] ?? null;
            $changes = [];
            foreach ($data as $key => $value) {
                if ($key !== 'creator' && !empty($value)) {
                    $changes[] = format_field_name($key) . ":  -> " . htmlspecialchars($value);
                }
            }
            if (!empty($changes)) {
                $change_string = implode("\n", $changes);
                $audit_query = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_value, new_value, ip_address, user_agent) VALUES (?, 'CREATE_FACILITY', 'USER', ?, NULL, ?, ?, ?)";
                $stmt = $db->prepare($audit_query);
                $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $change_string, $_SERVER['REMOTE_ADDR'] ?? '::1', $_SERVER['HTTP_USER_AGENT'] ?? '']);
            }
            header("Location: facilities.php?success=1");
            exit;
        }
        $message = $result['message'] ?? 'Failed to create facility';
        $message_type = 'error';
    } catch (Exception $e) {
        error_log("Add Facility Error: " . $e->getMessage());
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
    <title>Add Facility - Health Performance Monitoring System</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h1 { margin: 0 0 24px 0; color: #333; font-size: 24px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .form-group input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 2px rgba(102,126,234,0.2); }
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
    <div class="container">
        <a class="back-link" href="facilities.php">← Back to Facilities</a>
        <div class="card">
            <h1>Add Facility</h1>
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Facility Name *</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="facility_type">Type</label>
                        <input type="text" id="facility_type" name="facility_type" placeholder="e.g. Provincial Hospital" value="<?php echo htmlspecialchars($_POST['facility_type'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="province">Province</label>
                        <input type="text" id="province" name="province" placeholder="e.g. Leyte" value="<?php echo htmlspecialchars($_POST['province'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="municipality">Municipality/City</label>
                    <input type="text" id="municipality" name="municipality" placeholder="e.g. Tacloban" value="<?php echo htmlspecialchars($_POST['municipality'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="color_legend">Facility Color</label>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <input type="color" id="color_legend_picker" name="color_legend_display" value="<?php echo htmlspecialchars($_POST['color_legend'] ?? '#2e7d32'); ?>" style="width: 60px; height: 45px; cursor: pointer; border: 1px solid #ddd; border-radius: 4px;">
                        <input type="text" id="color_legend" name="color_legend" value="<?php echo htmlspecialchars($_POST['color_legend'] ?? '#2e7d32'); ?>" placeholder="#2e7d32" style="flex: 1;" pattern="^#[0-9A-Fa-f]{6}$">
                    </div>
                    <div style="font-size: 12px; color: #999; margin-top: 4px;">Use hex color code (e.g., #2e7d32). Color picker updates the text field.</div>
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
                    <input type="text" id="contact_person" name="contact_person" value="<?php echo htmlspecialchars($_POST['contact_person'] ?? ''); ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_email">Contact Email</label>
                        <input type="email" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($_POST['contact_email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="contact_phone">Contact Phone</label>
                        <input type="text" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($_POST['contact_phone'] ?? ''); ?>">
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Create Facility</button>
                    <a href="facilities.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
