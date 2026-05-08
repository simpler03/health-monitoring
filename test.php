<?php
// XAMPP Test File
?>
<!DOCTYPE html>
<html>
<head>
    <title>XAMPP Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; color: #155724; }
        .info { background: #cfe2ff; border: 1px solid #b6d4fe; padding: 15px; margin: 15px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="success">
        <h1>✓ XAMPP is Working!</h1>
        <p>Your PHP installation is functioning correctly.</p>
    </div>
    
    <div class="info">
        <h2>Next Steps:</h2>
        <ol>
            <li>Download the complete project ZIP from v0</li>
            <li>Extract it to: <code>C:\xampp\htdocs\health-monitoring\</code></li>
            <li>Run the database setup script</li>
            <li>Access: <code>http://localhost/health-monitoring/public/login.php</code></li>
        </ol>
    </div>

    <div class="info">
        <h2>System Information:</h2>
        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
        <p><strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
        <p><strong>Current Directory:</strong> <?php echo __DIR__; ?></p>
    </div>
</body>
</html>
