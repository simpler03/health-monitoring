<?php
/**
 * Logout Handler
 */

require_once __DIR__ . '/config/Auth.php';
require_once __DIR__ . '/config/Database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $database = new Database();
    $db = $database->connect();
    $auth = new Auth($db);
    $auth->logout();
} catch (Exception $e) {
    // Even if DB connection fails, destroy session
    session_destroy();
}

header("Location: login.php");
exit;
