<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Path Configuration Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f7fa;
        }
        .test-card {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: #2e7d32; }
        .error { color: #c62828; }
        h1 { color: #333; }
        h2 { color: #667eea; }
        pre {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .test-item {
            margin: 10px 0;
            padding: 10px;
            border-left: 4px solid #667eea;
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>🔧 Path Configuration Test</h1>
    
    <div class="test-card">
        <h2>1. Base Configuration Test</h2>
        <?php
        try {
            require_once __DIR__ . '/config/BaseConfig.php';
            echo '<div class="test-item success">✓ BaseConfig.php loaded successfully</div>';
            echo '<pre>';
            echo 'BASE_PATH: ' . BASE_PATH . "\n";
            echo 'BASE_URL: ' . BASE_URL . "\n";
            echo '</pre>';
        } catch (Exception $e) {
            echo '<div class="test-item error">✗ Error: ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>

    <div class="test-card">
        <h2>2. Database Configuration Test</h2>
        <?php
        try {
            require_once __DIR__ . '/config/Database.php';
            echo '<div class="test-item success">✓ Database.php loaded successfully</div>';
            
            $database = new Database();
            $db = $database->connect();
            echo '<div class="test-item success">✓ Database connection successful</div>';
        } catch (Exception $e) {
            echo '<div class="test-item error">✗ Database Error: ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>

    <div class="test-card">
        <h2>3. Auth Configuration Test</h2>
        <?php
        try {
            require_once __DIR__ . '/config/Auth.php';
            echo '<div class="test-item success">✓ Auth.php loaded successfully</div>';
        } catch (Exception $e) {
            echo '<div class="test-item error">✗ Error: ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>

    <div class="test-card">
        <h2>4. Asset Path Test</h2>
        <div class="test-item">
            <strong>CSS Path:</strong> <?php echo asset('css/styles.css'); ?><br>
            <strong>Expected:</strong> /health-monitoring-deploy/public/css/styles.css<br>
            <?php 
            if (asset('css/styles.css') === '/health-monitoring-deploy/public/css/styles.css') {
                echo '<span class="success">✓ Path is correct</span>';
            } else {
                echo '<span class="error">✗ Path is incorrect</span>';
            }
            ?>
        </div>
    </div>

    <div class="test-card">
        <h2>5. File Existence Tests</h2>
        <?php
        $files_to_check = [
            'config/BaseConfig.php',
            'config/Auth.php',
            'config/Database.php',
            'config/EvaluationManager.php',
            'config/FacilityManager.php',
            'config/QueryHelper.php',
            'css/styles.css',
            'dashboard.php',
            'login.php',
            'index.php'
        ];

        foreach ($files_to_check as $file) {
            $full_path = __DIR__ . '/' . $file;
            if (file_exists($full_path)) {
                echo '<div class="test-item success">✓ ' . htmlspecialchars($file) . ' exists</div>';
            } else {
                echo '<div class="test-item error">✗ ' . htmlspecialchars($file) . ' NOT FOUND</div>';
            }
        }
        ?>
    </div>

    <div class="test-card">
        <h2>6. Apache Configuration</h2>
        <div class="test-item">
            <strong>Current URL:</strong> <?php echo $_SERVER['REQUEST_URI']; ?><br>
            <strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?><br>
            <strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?><br>
            <strong>Script Filename:</strong> <?php echo __FILE__; ?>
        </div>
    </div>

    <div class="test-card">
        <h2>✅ Test Complete</h2>
        <p>If all tests above show <span class="success">✓</span> marks, your path configuration is working correctly!</p>
        <p><strong>Next Step:</strong> Try accessing <a href="login.php">Login Page</a> or <a href="dashboard.php">Dashboard</a></p>
    </div>
</body>
</html>
