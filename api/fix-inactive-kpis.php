<?php
/**
 * Fix inactive KPIs/Building Blocks
 * Marks all as active so they display for evaluations
 */

require_once __DIR__ . '/../config/BaseConfig.php';
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->connect();

try {
    // Activate all building blocks
    $stmt = $db->query("UPDATE building_blocks SET is_active = 1");
    echo "✓ Building Blocks: All marked active\n";

    // Activate all strategic objectives
    $stmt = $db->query("UPDATE strategic_objectives SET is_active = 1");
    echo "✓ Strategic Objectives: All marked active\n";

    // Activate all KPIs
    $stmt = $db->query("UPDATE kpi_table SET is_active = 1");
    echo "✓ KPIs: All marked active\n";

    echo "\n✓ All data is now visible to evaluations!\n";
    echo "Try loading an evaluation again as a viewer.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
