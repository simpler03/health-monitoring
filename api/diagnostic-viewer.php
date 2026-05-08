<?php
/**
 * Diagnostic endpoint to check evaluation data for viewers
 * Usage: api/diagnostic-viewer.php?eval_id=1
 */

require_once __DIR__ . '/../config/BaseConfig.php';
require_once __DIR__ . '/../config/Auth.php';
Auth::requireLogin();

require_once __DIR__ . '/../config/Database.php';

$eval_id = isset($_GET['eval_id']) ? (int)$_GET['eval_id'] : 0;
if (!$eval_id) {
    echo "Missing eval_id parameter";
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    // Check if evaluation exists
    $stmt = $db->prepare("SELECT * FROM evaluations WHERE id = ?");
    $stmt->execute([$eval_id]);
    $eval = $stmt->fetch();

    echo "<h2>Evaluation Data</h2>";
    if ($eval) {
        echo "<pre>";
        print_r($eval);
        echo "</pre>";
    } else {
        echo "❌ Evaluation not found<br>";
    }

    // Check if KPIs exist for this evaluation
    echo "<h2>KPIs/Indicators Count</h2>";
    $stmt = $db->prepare("
        SELECT COUNT(*) as total,
               SUM(CASE WHEN k.is_active = 1 THEN 1 ELSE 0 END) as active,
               SUM(CASE WHEN k.is_active = 0 THEN 1 ELSE 0 END) as inactive
        FROM kpi_table k
        JOIN strategic_objectives so ON k.strategic_objective_id = so.id
        JOIN building_blocks bb ON so.building_block_id = bb.id
        WHERE k.is_active = 1 AND so.is_active = 1 AND bb.is_active = 1
    ");
    $stmt->execute();
    $kpi_status = $stmt->fetch();
    echo "<pre>";
    print_r($kpi_status);
    echo "</pre>";

    // Check if scores exist for this evaluation
    echo "<h2>Scores for Evaluation #$eval_id</h2>";
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM scores WHERE evaluation_id = ?");
    $stmt->execute([$eval_id]);
    $score_count = $stmt->fetch();
    echo "Total scores: " . $score_count['count'] . "<br>";

    // Sample indicators retrieved by getIndicatorsForEvaluation logic
    echo "<h2>Sample Indicators (as viewer would see)</h2>";
    $stmt = $db->prepare("
        SELECT k.id as key_performance_indicator_id, k.indicator_name, 
               so.name as objective_name, bb.name as building_block_name,
               s.score_value, s.percentage_value, s.remarks
        FROM kpi_table k
        JOIN strategic_objectives so ON k.strategic_objective_id = so.id
        JOIN building_blocks bb ON so.building_block_id = bb.id
        LEFT JOIN scores s ON s.key_performance_indicator_id = k.id AND s.evaluation_id = ?
        WHERE k.is_active = 1 AND so.is_active = 1 AND bb.is_active = 1
        LIMIT 5
    ");
    $stmt->execute([$eval_id]);
    $samples = $stmt->fetchAll();
    if ($samples) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Building Block</th><th>Objective</th><th>Indicator</th><th>Score</th><th>%</th></tr>";
        foreach ($samples as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['building_block_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['objective_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['indicator_name']) . "</td>";
            echo "<td>" . ($row['score_value'] !== null ? $row['score_value'] : '(null)') . "</td>";
            echo "<td>" . ($row['percentage_value'] !== null ? $row['percentage_value'] : '(null)') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ No active indicators found!<br>";
        echo "<p><strong>Possible causes:</strong></p>";
        echo "<ul>";
        echo "<li>Building blocks are not marked active (is_active = 0)</li>";
        echo "<li>Strategic objectives are not marked active</li>";
        echo "<li>KPIs are not marked active</li>";
        echo "</ul>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
