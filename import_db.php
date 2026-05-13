<?php
require 'db.php';

echo "Starting Database Import...\n";

try {
    $pdo = db();
    
    // First drop existing tables to prevent "already exists" errors
    echo "Cleaning up existing tables...\n";
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0;');
    $pdo->exec('DROP TABLE IF EXISTS admins, audit_logs, billings, clients, services, quotes, quote_items, chit_funds, chit_members, chit_payments;');
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');
    
    // Check for possible SQL filenames
    $possibleFiles = ['u874184579_renewalswebapp.sql', 'amc_hosting.sql', 'u874184579_renewalswebapp_db.sql'];
    $sqlFile = '';
    
    foreach ($possibleFiles as $file) {
        if (file_exists(__DIR__ . '/' . $file)) {
            $sqlFile = __DIR__ . '/' . $file;
            break;
        }
    }

    if ($sqlFile) {
        echo "Importing from: " . basename($sqlFile) . "...\n";
        $sql = file_get_contents($sqlFile);
        if ($sql) {
            $pdo->exec($sql);
            echo "Successfully imported the SQL dump.\n";
        } else {
            echo "Error: Could not read content from $sqlFile\n";
        }
    } else {
        echo "Error: No SQL dump file found. Looked for: " . implode(', ', $possibleFiles) . "\n";
    }
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}

