<?php
/**
 * Facility Summary Report - Building Block Performance Analysis
 * Health Performance Monitoring System
 */

require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
Auth::requireLogin();

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/QueryHelper.php';

$user = $_SESSION;
$evaluation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$evaluation_id) {
    header("Location: evaluations.php");
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();
    $query_helper = new QueryHelper($db);

    // Get evaluation details
    $evaluation = $query_helper->getEvaluationDetails($evaluation_id);
    if (!$evaluation) {
        header("Location: evaluations.php");
        exit;
    }

    // Get all building block statistics
    $building_blocks = $query_helper->getAllBuildingBlockStats($evaluation_id);
    
    // Calculate overall compliance score
    $overall_stats = $query_helper->calculateFacilityScore($evaluation_id);
    $overall_percentage = $overall_stats['overall_percentage'] ?? 0;
    $overall_complied = $overall_stats['complied_scores'] ?? 0;
    $overall_total = $overall_stats['total_scores'] ?? 0;
    
    // Get performance rating
    $rating_info = $query_helper->getPerformanceRating($overall_percentage);
    
    // Calculate weighted score if needed
    $weighted_score = 0;
    foreach ($building_blocks as $bb) {
        $bb_percentage = $bb['average_score'] ?? 0;
        $weight = ($bb['weight_percentage'] ?? 0) / 100;
        $weighted_score += ($bb_percentage * $weight);
    }

} catch (Exception $e) {
    die("Error: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($evaluation['facility_name']); ?> - Summary Report</title>
    <link rel="stylesheet" href="<?php echo asset('css/styles.css'); ?>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 5px;
        }

        .facility-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .info-item {
            font-size: 14px;
        }

        .info-label {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 3px;
        }

        .info-value {
            color: #555;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .score-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .score-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .score-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .score-value {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .score-label {
            font-size: 12px;
            color: #999;
            margin-bottom: 15px;
        }

        .rating-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            color: white;
        }

        .blue { color: #667eea; }
        .green { color: #2e7d32; }
        .orange { color: #f57c00; }
        .red { color: #c62828; }

        .building-blocks {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .building-blocks h2 {
            font-size: 20px;
            margin-bottom: 25px;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .bb-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100%, 1fr));
            gap: 20px;
        }

        .bb-item {
            border-left: 4px solid #667eea;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .bb-item:hover {
            background: #f0f0f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .bb-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .bb-name {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .bb-weight {
            font-size: 12px;
            background: #667eea;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .bb-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .stat-box {
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 4px;
        }

        .stat-label {
            color: #999;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #333;
        }

        .progress-bar {
            width: 100%;
            height: 24px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .progress-fill {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 11px;
            font-weight: 600;
        }

        .progress-text {
            font-size: 12px;
            color: #666;
            display: flex;
            justify-content: space-between;
        }

        .controls {
            display: flex;
            gap: 10px;
            justify-content: center;
            padding: 20px;
            background: white;
            border-radius: 8px;
            margin-top: 30px;
        }

        .controls a, .controls button {
            margin: 0 5px;
        }

        @media print {
            body { background: white; }
            .controls { display: none; }
            .btn-print { display: none; }
        }

        .ai-summary {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .ai-summary h2 {
            font-size: 20px;
            margin-bottom: 25px;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .ai-insights {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .insight-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .insight-card h3 {
            font-size: 16px;
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .insight-card p {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .insight-card ul {
            color: #555;
            padding-left: 20px;
        }

        .insight-card li {
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .trend-indicator {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }

        .trend-label {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            white-space: nowrap;
        }

        .trend-value {
            font-size: 14px;
            color: #667eea;
            font-weight: 600;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div>
                    <h1><?php echo htmlspecialchars($evaluation['facility_name']); ?></h1>
                    <p style="color: #999; margin-top: 5px;">Summary Report</p>
                </div>
                <button class="btn btn-secondary btn-print" onclick="window.print()">Print Report</button>
            </div>

            <div class="facility-info">
                <div class="info-item">
                    <div class="info-label">Facility Type</div>
                    <div class="info-value"><?php echo htmlspecialchars($evaluation['facility_type'] ?? 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Province</div>
                    <div class="info-value"><?php echo htmlspecialchars($evaluation['province'] ?? 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Municipality</div>
                    <div class="info-value"><?php echo htmlspecialchars($evaluation['municipality'] ?? 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Evaluation Date</div>
                    <div class="info-value"><?php echo date('Y-m-d', strtotime($evaluation['created_at'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Evaluator</div>
                    <div class="info-value"><?php echo htmlspecialchars($evaluation['evaluator_name'] ?? 'System'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value"><?php echo ucfirst(htmlspecialchars($evaluation['status'] ?? 'draft')); ?></div>
                </div>
            </div>
        </div>

        <!-- Overall Score Cards -->
        <div class="score-cards">
            <div class="score-card">
                <h3>Overall Compliance Score</h3>
                <div class="score-value blue"><?php echo number_format($overall_percentage, 1); ?>%</div>
                <div class="score-label"><?php echo $overall_complied; ?> of <?php echo $overall_total; ?> indicators</div>
            </div>

            <div class="score-card">
                <h3>Performance Rating</h3>
                <div class="score-value" style="color: <?php echo $rating_info['color']; ?>; font-size: 32px;">
                    <?php echo $rating_info['rating']; ?>
                </div>
                <div class="score-label">Based on compliance percentage</div>
            </div>

            <div class="score-card">
                <h3>Weighted Score</h3>
                <div class="score-value blue"><?php echo number_format($weighted_score, 1); ?>%</div>
                <div class="score-label">Considering building block weights</div>
            </div>
        </div>

        <!-- AI-Driven Summary Section -->
        <div class="ai-summary">
            <h2>AI-Powered Performance Analysis</h2>
            <div class="ai-insights">
                <div class="insight-card">
                    <h3>📊 Performance Overview</h3>
                    <p><?php echo htmlspecialchars($evaluation['facility_name']); ?> demonstrates a <?php echo $rating_info['rating']; ?> performance level with an overall compliance score of <?php echo number_format($overall_percentage, 1); ?>%. This facility shows particular strength in <?php 
                        $strongest_bb = '';
                        $highest_score = 0;
                        foreach ($building_blocks as $bb) {
                            if (($bb['average_score'] ?? 0) > $highest_score) {
                                $highest_score = $bb['average_score'] ?? 0;
                                $strongest_bb = $bb['building_block_name'];
                            }
                        }
                        echo htmlspecialchars($strongest_bb);
                    ?> while areas for improvement include <?php
                        $weakest_bb = '';
                        $lowest_score = 100;
                        foreach ($building_blocks as $bb) {
                            if (($bb['average_score'] ?? 0) < $lowest_score) {
                                $lowest_score = $bb['average_score'] ?? 0;
                                $weakest_bb = $bb['building_block_name'];
                            }
                        }
                        echo htmlspecialchars($weakest_bb);
                    ?>.</p>
                </div>

                <div class="insight-card">
                    <h3>🎯 Key Recommendations</h3>
                    <ul>
                        <?php if ($overall_percentage < 60): ?>
                            <li><strong>Critical Priority:</strong> Immediate action required to address fundamental compliance gaps. Focus on basic infrastructure and essential services.</li>
                        <?php elseif ($overall_percentage < 80): ?>
                            <li><strong>High Priority:</strong> Strengthen core operational areas. Consider additional training and resource allocation for underperforming building blocks.</li>
                        <?php else: ?>
                            <li><strong>Maintenance Focus:</strong> Continue excellence in current practices while identifying opportunities for further optimization.</li>
                        <?php endif; ?>

                        <li><strong>Targeted Improvement:</strong> Prioritize enhancement of <?php echo htmlspecialchars($weakest_bb); ?> through dedicated quality improvement initiatives.</li>
                        <li><strong>Best Practice Sharing:</strong> Leverage successful strategies from <?php echo htmlspecialchars($strongest_bb); ?> across other building blocks.</li>
                    </ul>
                </div>

                <div class="insight-card">
                    <h3>📈 Trend Analysis</h3>
                    <p>Based on current performance patterns, this facility is positioned to <?php 
                        if ($overall_percentage >= 80) {
                            echo "maintain high standards with potential for excellence in specialized areas.";
                        } elseif ($overall_percentage >= 60) {
                            echo "achieve significant improvement with focused interventions and resource commitment.";
                        } else {
                            echo "require comprehensive support and systematic quality improvement programs.";
                        }
                    ?></p>
                    <div class="trend-indicator">
                        <span class="trend-label">Improvement Potential:</span>
                        <div class="progress-bar">
                            <div class="progress-fill" style="background: <?php echo $rating_info['color']; ?>; width: <?php echo min(100 - $overall_percentage, 100); ?>%;"></div>
                        </div>
                        <span class="trend-value"><?php echo number_format(100 - $overall_percentage, 1); ?>% remaining</span>
                    </div>
                </div>

                <div class="insight-card">
                    <h3>🏥 Comparative Insights</h3>
                    <p>This facility's performance in <?php echo htmlspecialchars($evaluation['province'] ?? 'the region'); ?> ranks in the <?php 
                        if ($overall_percentage >= 85) {
                            echo "top quartile, demonstrating exemplary practices that could serve as models for peer facilities.";
                        } elseif ($overall_percentage >= 70) {
                            echo "upper-middle range, with solid foundations for advancement to leading performance.";
                        } elseif ($overall_percentage >= 50) {
                            echo "middle range, indicating opportunities for significant improvement through targeted interventions.";
                        } else {
                            echo "lower quartile, requiring immediate comprehensive support and quality improvement initiatives.";
                        }
                    ?></p>
                </div>
            </div>
        </div>

            <div class="bb-grid">
                <?php foreach ($building_blocks as $bb): 
                    $bb_percentage = $bb['average_score'] ?? 0;
                    $bb_color = $bb_percentage >= 80 ? '#2e7d32' : ($bb_percentage >= 60 ? '#f57c00' : '#c62828');
                ?>
                <div class="bb-item">
                    <div class="bb-header">
                        <div>
                            <div class="bb-name"><?php echo htmlspecialchars($bb['building_block_name']); ?></div>
                            <div style="font-size: 12px; color: #999; margin-top: 3px;">Code: <?php echo htmlspecialchars($bb['code']); ?></div>
                        </div>
                        <div class="bb-weight"><?php echo number_format($bb['weight_percentage']); ?>%</div>
                    </div>

                    <div class="bb-stats">
                        <div class="stat-box">
                            <div class="stat-label">Complied</div>
                            <div class="stat-value"><?php echo $bb['complied_count'] ?? 0; ?></div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-label">Total</div>
                            <div class="stat-value"><?php echo $bb['total_indicators'] ?? 0; ?></div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-label">Score %</div>
                            <div class="stat-value"><?php echo number_format($bb_percentage, 0); ?>%</div>
                        </div>
                    </div>

                    <div class="progress-bar">
                        <div class="progress-fill" style="background: <?php echo $bb_color; ?>; width: <?php echo $bb_percentage; ?>%;">
                            <?php if ($bb_percentage > 10): echo number_format($bb_percentage, 0) . '%'; endif; ?>
                        </div>
                    </div>

                    <div class="progress-text">
                        <span><?php echo number_format($bb['complied_count'] ?? 0); ?> / <?php echo number_format($bb['total_indicators'] ?? 0); ?> indicators met</span>
                        <span><?php echo htmlspecialchars(substr($bb['building_block_name'], 0, 20)); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Navigation -->
        <div class="controls">
            <a href="evaluations.php" class="btn btn-secondary">Back to Evaluations</a>
            <a href="evaluation-view.php?id=<?php echo (int)$evaluation_id; ?>" class="btn btn-primary">View Full Evaluation</a>
        </div>
            </div>
        </div>
    </div>
</body>
</html>
