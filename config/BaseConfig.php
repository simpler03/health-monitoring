<?php
/**
 * Base URL Configuration
 * Health Performance Monitoring System
 */

// Define the base path for the application based on the current script location.
// This keeps asset and page URLs correct whether the app is served from the
// project root or a nested subdirectory.
$scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = rtrim($scriptDirectory, '/');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}

define('BASE_PATH', $basePath);
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . BASE_PATH);

// Helper function to generate URLs
function url($path = '') {
    return (BASE_PATH ?: '') . '/' . ltrim($path, '/');
}

// Helper function to generate asset URLs
function asset($path = '') {
    return (BASE_PATH ?: '') . '/' . ltrim($path, '/');
}
