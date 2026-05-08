<?php
/**
 * New Evaluation Form
 */

require_once __DIR__ . '/config/BaseConfig.php';
require_once 'config/Auth.php';
Auth::requireRole('input');

require_once 'config/Database.php';
require_once 'config/FacilityManager.php';
require_once 'config/EvaluationManager.php';

$message = '';
$error = '';

try {
    $database = new Database();
    $db = $database->connect();
    $facility_manager = new FacilityManager($db);
    $evaluation_manager = new EvaluationManager($db);

    $user = $_SESSION;
    
    // Get facilities based on user role
    if ($user['role'] === 'admin') {
        // Admins can see all active facilities
        $facilities = $facility_manager->getAllFacilities();
    } else {
        // Input users can only create evaluations for facilities they created, edited, or are assigned to
        $facilities = $facility_manager->getFacilitiesForUserEvaluation($user['user_id'], $user['facility_names'] ?? ($user['facility_name'] ?? null));
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate facility_id
        $facility_id = $_POST['facility_id'] ?? null;
        
        // For admins, allow any facility; for input users, check access
        if ($user['role'] === 'admin') {
            // Admins can create evaluations for any facility
            $selected_facility = null;
            foreach ($facilities as $f) {
                if ((int)$f['id'] === (int)$facility_id) {
                    $selected_facility = $f;
                    break;
                }
            }
            if (!$selected_facility) {
                $error = 'Selected facility not found';
            }
        } else {
            // Input users: check if selected facility is in their accessible facilities
            $selected_facility = null;
            foreach ($facilities as $f) {
                if ((int)$f['id'] === (int)$facility_id) {
                    $selected_facility = $f;
                    break;
                }
            }
            if (!$selected_facility) {
                $error = 'You do not have access to create evaluations for the selected facility';
            }
        }

        if (!$error) {
            $result = $evaluation_manager->createEvaluation([
                'facility_id' => $facility_id,
                'evaluation_date' => $_POST['evaluation_date'] ?? null,
                'evaluation_period' => $_POST['evaluation_period'] ?? null
            ]);

            if ($result['success']) {
                // Add audit log for evaluation creation
                $facility_name = $selected_facility['name'] ?? 'Unknown';
                $evaluation_date = $_POST['evaluation_date'] ?? '';
                $evaluation_period = $_POST['evaluation_period'] ?? '';
                
                $changes = [];
                $changes[] = "facility: " . htmlspecialchars($facility_name);
                $changes[] = "evaluation date: " . htmlspecialchars($evaluation_date);
                $changes[] = "evaluation period: " . htmlspecialchars($evaluation_period);
                
                $change_string = implode("\n", $changes);
                $audit_query = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_value, new_value, ip_address, user_agent) VALUES (?, 'CREATE_EVALUATION', 'USER', ?, NULL, ?, ?, ?)";
                $audit_stmt = $db->prepare($audit_query);
                $audit_stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $change_string, $_SERVER['REMOTE_ADDR'] ?? '::1', $_SERVER['HTTP_USER_AGENT'] ?? '']);
                
                header("Location: evaluation-view.php?id=" . $result['evaluation_id'] . "&edit=1");
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }

} catch (Exception $e) {
    error_log("New Evaluation Error: " . $e->getMessage());
    $error = 'An error occurred';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Evaluation - Health Performance Monitoring System</title>
    <link rel="stylesheet" href="<?php echo asset('css/styles.css'); ?>">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .form-card h1 {
            color: #333;
            margin-bottom: 25px;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: 'Segoe UI', sans-serif;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group select {
            cursor: pointer;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 100%;
            margin-top: 10px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #333;
            margin-right: 10px;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .button-group .btn-secondary {
            flex: 1;
        }

        .error-message {
            background-color: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .back-link {
            color: #667eea;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
            font-weight: 600;
        }

        .help-text {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="container">
                <a class="back-link" onclick="history.back()">← Back</a>

        <div class="form-card">
            <h1>Create New Evaluation</h1>

            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="new-evaluation.php">
                <div class="form-group">
                    <label for="facility_id">Select Facility *</label>
                    <select id="facility_id" name="facility_id" required>
                        <option value="">-- Choose a facility --</option>
                        <?php foreach ($facilities as $facility): ?>
                            <option value="<?php echo $facility['id']; ?>">
                                <?php echo htmlspecialchars($facility['name']); ?>
                                <?php if ($facility['municipality']): ?>
                                    (<?php echo htmlspecialchars($facility['municipality']); ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="help-text">Select the health facility for this evaluation</div>
                </div>

                <div class="form-group">
                    <label for="evaluation_date">Evaluation Date *</label>
                    <input type="date" id="evaluation_date" name="evaluation_date" required value="<?php echo date('Y-m-d'); ?>">
                    <div class="help-text">Date when the evaluation was conducted</div>
                </div>

                <div class="form-group">
                    <label for="evaluation_period">Evaluation Period</label>
                    <input type="text" id="evaluation_period" name="evaluation_period" placeholder="e.g., 2024-01" value="<?php echo date('Y-m'); ?>">
                    <div class="help-text">Month or period covered by this evaluation (YYYY-MM)</div>
                </div>

                <div class="button-group">
                    <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Evaluation</button>
                </div>
            </form>
            </div>
        </div>
    </div>
</body>
</html>
