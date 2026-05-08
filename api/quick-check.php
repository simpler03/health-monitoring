<?php
// Quick check: Are KPIs, building blocks, and objectives active?
require_once __DIR__ . '/../config/BaseConfig.php';
require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->connect();

// Check building blocks
$stmt = $db->query("SELECT COUNT(*) as total, SUM(is_active) as active FROM building_blocks");
$bb = $stmt->fetch();
echo "Building Blocks: Total=$bb[total], Active=$bb[active]\n";

// Check strategic objectives
$stmt = $db->query("SELECT COUNT(*) as total, SUM(is_active) as active FROM strategic_objectives");
$so = $stmt->fetch();
echo "Strategic Objectives: Total=$so[total], Active=$so[active]\n";

// Check KPIs
$stmt = $db->query("SELECT COUNT(*) as total, SUM(is_active) as active FROM kpi_table");
$kpi = $stmt->fetch();
echo "KPIs: Total=$kpi[total], Active=$kpi[active]\n";

// Check evaluations
$stmt = $db->query("SELECT COUNT(*) as total FROM evaluations");
$eval = $stmt->fetch();
echo "Evaluations: Total=$eval[total]\n";

// Check scores
$stmt = $db->query("SELECT COUNT(*) as total FROM scores");
$score = $stmt->fetch();
echo "Scores: Total=$score[total]\n";
?>
