<?php
/**
 * User Profile Page
 */

require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
Auth::requireLogin();

$user = $_SESSION;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Health Performance Monitoring System</title>
    <link rel="stylesheet" href="<?php echo asset('css/styles.css'); ?>">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .card h1 { color: #333; margin-bottom: 25px; }
        .info-group { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .info-label { font-size: 12px; color: #999; text-transform: uppercase; font-weight: 600; }
        .info-value { font-size: 16px; color: #333; font-weight: 500; margin-top: 5px; }
        .back-link { color: #667eea; text-decoration: none; display: inline-block; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="container">
                <a class="back-link" onclick="history.back()">← Back</a>
        <div class="card">
            <h1>User Profile</h1>
            <div class="info-group">
                <div class="info-label">Full Name</div>
                <div class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></div>
            </div>
            <div class="info-group">
                <div class="info-label">Email</div>
                <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <div class="info-group">
                <div class="info-label">Username</div>
                <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
            </div>
            <div class="info-group">
                <div class="info-label">Role</div>
                <div class="info-value"><?php echo ucfirst($user['role']); ?></div>
            </div>
            <div class="info-group">
                <div class="info-label">Facility</div>
            <div class="info-value"><?php echo htmlspecialchars(!empty($user['facility_names']) ? implode(', ', $user['facility_names']) : ($user['facility_name'] ?? 'N/A')); ?></div>
            </div>
            </div>
        </div>
    </div>
</body>
</html>
