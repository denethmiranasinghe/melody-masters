<?php
require_once 'includes/db.php';
try {
    $pdo->exec('ALTER TABLE products ADD COLUMN shipping_cost DECIMAL(10, 2) NOT NULL DEFAULT 0.00');
    echo 'Done';
} catch (Exception $e) {
    echo $e->getMessage();
}
