<?php
/**
 * Utility Script to Hide Health Information Systems Strategic Objective
 * Hides "Health Information Systems" from Building Block "Information Communication and Technology"
 */

require_once 'config/Database.php';

try {
    $database = new Database();
    $db = $database->connect();
    
    // Hide the "Health Information Systems" strategic objective (ID 7) 
    // from Building Block "Information Communication and Technology" (ID 6)
    $query = "UPDATE strategic_objectives 
              SET is_active = 0 
              WHERE id = 7 AND name = 'Health Information Systems' AND building_block_id = 6";
    
    $stmt = $db->prepare($query);
    $result = $stmt->execute();
    
    if ($result && $stmt->rowCount() > 0) {
        echo "✓ Success: 'Health Information Systems' has been hidden from evaluations.\n";
        echo "Rows affected: " . $stmt->rowCount() . "\n";
    } else {
        echo "✗ No changes made. The strategic objective may not exist or is already hidden.\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
