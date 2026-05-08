<?php
/**
 * Unauthorized Access Page
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized - Health Performance Monitoring System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 500px;
        }

        .error-code {
            font-size: 72px;
            font-weight: bold;
            color: #667eea;
            margin: 0;
            line-height: 1;
        }

        h1 {
            color: #333;
            margin: 15px 0;
            font-size: 24px;
        }

        p {
            color: #666;
            margin: 15px 0;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: transform 0.2s;
            margin-top: 20px;
        }

        .btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <p class="error-code">403</p>
        <h1>Access Denied</h1>
        <p>You do not have permission to access this resource.</p>
        <p>Your current role does not allow access to this page.</p>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>
</body>
</html>
