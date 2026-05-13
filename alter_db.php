<?php
require 'db.php';
try {
    $pdo = db();
    $pdo->exec("ALTER TABLE clients ADD COLUMN IF NOT EXISTS renewal_date DATE NULL;");
    echo "Column 'renewal_date' added successfully! You can go back to dashboard.php now.";
} catch (Exception $e) {
    echo "Error adding column: " . $e->getMessage();
}
