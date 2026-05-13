<?php
echo "Starting File-System Cleanup for MySQL...\n";

$dataDir = 'D:\\php\\mysql\\data\\amc_hosting';
$files = ['admins.ibd', 'audit_logs.ibd', 'billings.ibd', 'clients.ibd', 'services.ibd'];

foreach ($files as $file) {
    $path = $dataDir . DIRECTORY_SEPARATOR . $file;
    if (file_exists($path)) {
        echo "Deleting $file... ";
        if (unlink($path)) {
            echo "Success.\n";
        } else {
            echo "FAILED (file may be locked by MySQL).\n";
        }
    } else {
        echo "$file not found, skipping.\n";
    }
}

echo "\nNow attempting to import the database...\n";
require 'db.php';

try {
    $pdo = db();
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0;');
    
    $sqlFile = __DIR__ . '/u874184579_renewalswebapp.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found at $sqlFile");
    }

    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);
    
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');
    echo "\nSUCCESS: Database imported successfully!\n";
} catch (Exception $e) {
    echo "\nIMPORT ERROR: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), '1813') !== false) {
        echo "\nIMPORTANT: The files were likely locked. Please STOP your MySQL service (via XAMPP Control Panel), then run this script again, then START MySQL.\n";
    }
}
