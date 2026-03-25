<?php
require_once 'includes/functions.php';

try {
    $pdo->exec("ALTER TABLE products ADD COLUMN download_link VARCHAR(255) DEFAULT NULL");
    echo "Column download_link added successfully to products table.";
} catch (PDOException $e) {
    echo "Error or already exists: " . $e->getMessage();
}
