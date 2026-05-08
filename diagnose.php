<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Health Monitoring System - Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { color: #333; }
        .check { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .pass { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .fail { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
        .section { margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Health Monitoring System - System Diagnostic</h1>
        
        <div class="section">
            <h2>System Checks</h2>
            
            <!-- PHP Version Check -->
            <div class="check pass">
                <strong>✓ PHP Installation</strong><br>
                PHP Version: <?php echo phpversion(); ?>
            </div>
            
            <!-- File Location Check -->
            <div class="check info">
                <strong>Current Location:</strong><br>
                <?php echo __DIR__; ?>
            </div>
            
            <!-- Database Configuration Check -->
            <div class="check <?php echo file_exists(__DIR__ . '/config/Database.php') ? 'pass' : 'fail'; ?>">
                <strong><?php echo file_exists(__DIR__ . '/config/Database.php') ? '✓' : '✗'; ?> Database Configuration File</strong><br>
                File: <code>/config/Database.php</code><br>
                Status: <?php echo file_exists(__DIR__ . '/config/Database.php') ? 'FOUND' : 'NOT FOUND'; ?>
            </div>
            
            <!-- Auth File Check -->
            <div class="check <?php echo file_exists(__DIR__ . '/config/Auth.php') ? 'pass' : 'fail'; ?>">
                <strong><?php echo file_exists(__DIR__ . '/config/Auth.php') ? '✓' : '✗'; ?> Authentication File</strong><br>
                File: <code>/config/Auth.php</code><br>
                Status: <?php echo file_exists(__DIR__ . '/config/Auth.php') ? 'FOUND' : 'NOT FOUND'; ?>
            </div>
            
            <!-- Login Page Check -->
            <div class="check <?php echo file_exists(__DIR__ . '/login.php') ? 'pass' : 'fail'; ?>">
                <strong><?php echo file_exists(__DIR__ . '/login.php') ? '✓' : '✗'; ?> Login Page</strong><br>
                File: <code>/login.php</code><br>
                Status: <?php echo file_exists(__DIR__ . '/login.php') ? 'FOUND' : 'NOT FOUND'; ?>
            </div>
            
            <!-- CSS File Check -->
            <div class="check <?php echo file_exists(__DIR__ . '/css/styles.css') ? 'pass' : 'fail'; ?>">
                <strong><?php echo file_exists(__DIR__ . '/css/styles.css') ? '✓' : '✗'; ?> CSS Styles</strong><br>
                File: <code>/css/styles.css</code><br>
                Status: <?php echo file_exists(__DIR__ . '/css/styles.css') ? 'FOUND' : 'NOT FOUND'; ?>
            </div>
            
            <!-- API Files Check -->
            <div class="check <?php echo (file_exists(__DIR__ . '/api/save_score.php') && file_exists(__DIR__ . '/api/get_evaluation_data.php')) ? 'pass' : 'fail'; ?>">
                <strong><?php echo (file_exists(__DIR__ . '/api/save_score.php') && file_exists(__DIR__ . '/api/get_evaluation_data.php')) ? '✓' : '✗'; ?> API Endpoints</strong><br>
                Files: <code>/api/save_score.php</code>, <code>/api/get_evaluation_data.php</code><br>
                Status: <?php echo (file_exists(__DIR__ . '/api/save_score.php') && file_exists(__DIR__ . '/api/get_evaluation_data.php')) ? 'FOUND' : 'NOT FOUND'; ?>
            </div>
        </div>
        
        <div class="section">
            <h2>Configuration Check</h2>
            <div class="check info">
                <strong>Database Connection Status:</strong><br>
                <?php
                // Try to load Database class
                if (file_exists(__DIR__ . '/config/Database.php')) {
                    try {
                        require_once __DIR__ . '/config/Database.php';
                        $db = new Database();
                        $conn = $db->getConnection();
                        if ($conn) {
                            echo '<div class="pass" style="margin-top: 10px;">✓ Database connection successful!</div>';
                        } else {
                            echo '<div class="fail" style="margin-top: 10px;">✗ Database connection failed. Check Database.php credentials.</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="fail" style="margin-top: 10px;">✗ Error: ' . $e->getMessage() . '</div>';
                    }
                } else {
                    echo '<div class="fail" style="margin-top: 10px;">✗ Database.php not found</div>';
                }
                ?>
            </div>
        </div>
        
        <div class="section">
            <h2>Next Steps</h2>
            <div class="check info">
                <?php
                $all_files_exist = file_exists(__DIR__ . '/config/Database.php') && 
                                   file_exists(__DIR__ . '/config/Auth.php') && 
                                   file_exists(__DIR__ . '/login.php') && 
                                   file_exists(__DIR__ . '/css/styles.css') && 
                                   file_exists(__DIR__ . '/api/save_score.php');
                
                if ($all_files_exist) {
                    echo '<p><strong>✓ All files are in place!</strong></p>';
                    echo '<ol>';
                    echo '<li>Ensure MySQL is running in XAMPP Control Panel</li>';
                    echo '<li>Run the database setup script: <code>scripts/01-create-database.sql</code></li>';
                    echo '<li>Go to: <a href="login.php">login.php</a></li>';
                    echo '<li>Login with: <strong>admin</strong> / <strong>admin123</strong></li>';
                    echo '</ol>';
                } else {
                    echo '<p><strong>✗ Some files are missing!</strong></p>';
                    echo '<p>Please make sure you have extracted all files from the v0-project.zip to:</p>';
                    echo '<code>C:\\xampp\\htdocs\\health-monitoring\\</code>';
                    echo '<p>Missing files:';
                    echo '<ul>';
                    if (!file_exists(__DIR__ . '/config/Database.php')) echo '<li>config/Database.php</li>';
                    if (!file_exists(__DIR__ . '/config/Auth.php')) echo '<li>config/Auth.php</li>';
                    if (!file_exists(__DIR__ . '/login.php')) echo '<li>login.php</li>';
                    if (!file_exists(__DIR__ . '/css/styles.css')) echo '<li>css/styles.css</li>';
                    if (!file_exists(__DIR__ . '/api/save_score.php')) echo '<li>api/save_score.php</li>';
                    echo '</ul>';
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
