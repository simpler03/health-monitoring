<?php
/**
 * Evaluations List Page
 */

require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
Auth::requireLogin();

$user = $_SESSION;
$current_role = $user['role'] ?? 'viewer';
// Allow input and viewer roles to access evaluations
if (!in_array($current_role, ['admin', 'input', 'viewer'])) {
    header("Location: unauthorized.php");
    exit;
}
// Viewer can only view, not edit/create
$can_edit = in_array($current_role, ['admin', 'input']);

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/EvaluationManager.php';

$user = $_SESSION;

try {
    $database = new Database();
    $db = $database->connect();
    $manager = new EvaluationManager($db);

    // Get evaluations based on user access
    if ($current_role === 'admin') {
        $evaluations = $manager->getEvaluations([]);
    } else {
        // For input and viewer, show evaluations for facilities they created, edited, or are assigned to
        $evaluations = $manager->getEvaluationsForUser($user['user_id'], $user['facility_names'] ?? ($user['facility_name'] ?? null));
    }

} catch (Exception $e) {
    error_log("Evaluations Error: " . $e->getMessage());
    $evaluations = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluations - Health Performance Monitoring System</title>
    <link rel="stylesheet" href="<?php echo asset('css/styles.css'); ?>">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            color: #333;
            margin: 0;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        .section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        table tr:hover {
            background: #f9f9f9;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-completed { background: #e8f5e9; color: #2e7d32; }
        .badge-draft { background: #fff3e0; color: #e65100; }
        .badge-approved { background: #e3f2fd; color: #1565c0; }
        
        .action-links a {
            color: #667eea;
            text-decoration: none;
            margin-right: 15px;
            font-size: 13px;
            font-weight: 600;
        }

        .back-link {
            color: #667eea;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
            font-weight: 600;
        }

        /* Approval Status Row Colors */
        tbody tr.row-approved {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
        }

        tbody tr.row-approved:hover {
            background-color: #c3e6cb;
        }

        tbody tr.row-rejected {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
        }

        tbody tr.row-rejected:hover {
            background-color: #f5c6cb;
        }

        /* Legend Styles */
        .approval-legend {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
            border-left: 4px solid #667eea;
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .legend-color {
            width: 24px;
            height: 24px;
            border-radius: 3px;
            border-left: 4px solid transparent;
        }

        .legend-color.approved {
            background-color: #d4edda;
            border-left-color: #28a745;
        }

        .legend-color.rejected {
            background-color: #f8d7da;
            border-left-color: #dc3545;
        }

        .legend-color.neutral {
            background-color: #ffffff;
            border: 1px solid #ddd;
        }

        .legend-label {
            font-size: 13px;
            color: #666;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="container">
                <a class="back-link" href="dashboard.php">← Back to Dashboard</a>

        <div class="header">
            <h1>Evaluations</h1>
            <?php if ($can_edit): ?>
                <a href="new-evaluation.php" class="btn btn-primary">+ New Evaluation</a>
            <?php endif; ?>
        </div>

        <div class="section">
            <?php if (count($evaluations) > 0): ?>
                <div class="approval-legend">
                    <div class="legend-item">
                        <div class="legend-color approved"></div>
                        <span class="legend-label">Approved</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color rejected"></div>
                        <span class="legend-label">Rejected</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color neutral"></div>
                        <span class="legend-label">Pending Review</span>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Facility</th>
                            <th>Date</th>
                            <th>Last Updated</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($evaluations as $eval): 
                            // Convert to Philippine time (UTC+8)
                            $updated_time = new DateTime($eval['updated_at'], new DateTimeZone('UTC'));
                            $updated_time->setTimezone(new DateTimeZone('Asia/Manila'));
                            $formatted_time = $updated_time->format('m/d/Y h:i A');
                            // Determine row class based on approval status
                            $row_class = '';
                            if (isset($eval['approved'])) {
                                if ($eval['approved'] == 1) {
                                    $row_class = 'row-approved';
                                } elseif ($eval['approved'] == 2) {
                                    $row_class = 'row-rejected';
                                }
                            }
                        ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td><?php echo htmlspecialchars($eval['facility_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($eval['evaluation_date'])); ?></td>
                                <td><?php echo $formatted_time; ?></td>
                                <td><span class="badge badge-<?php echo $eval['status']; ?>"><?php echo ucfirst($eval['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($eval['created_by_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <div class="action-links">
                                        <a href="evaluation-view.php?id=<?php echo $eval['id']; ?>">View</a>
                                        <a href="facility-summary.php?id=<?php echo $eval['id']; ?>">Summary</a>
                                        <?php if ($can_edit && $eval['status'] !== 'completed'): ?>
                                            <a href="evaluation-edit.php?id=<?php echo $eval['id']; ?>">Edit</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No evaluations yet. <a href="new-evaluation.php">Create one now</a></p>
            <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
