<?php
/**
 * Evaluation Edit - Redirect to evaluation view (edit happens there)
 * Web-Based Health Performance Scoring and Monitoring System
 */

require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
Auth::requireLogin();

$evaluation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$evaluation_id) {
    header('Location: evaluations.php');
    exit;
}

header('Location: evaluation-view.php?id=' . $evaluation_id . '&edit=1');
exit;
