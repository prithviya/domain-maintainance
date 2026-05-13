<?php
declare(strict_types=1);

function isDebugMode(): bool
{
    $env = getenv('APP_DEBUG');
    if ($env === '1' || $env === 'true') {
        return true;
    }
    return isset($_GET['debug']) && $_GET['debug'] === '1';
}

function logAppError(string $message): void
{
    $logDir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . DIRECTORY_SEPARATOR . 'app_error.log';
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND);
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    /*
    Update these DB values manually after importing schema.sql
    */
    // Live Server Details
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = (int)(getenv('DB_PORT') ?: 3306);
    $database = getenv('DB_NAME') ?: 'u874184579_renewalswebapp';
    $username = getenv('DB_USER') ?: 'u874184579_renewalswebapp';
    $password = getenv('DB_PASS') ?: '@@All4meee2026';

    /*
    // Local Dev Details
    $host = 'localhost';
    $port = 3308;
    $database = 'amc_hosting';
    $username = 'root';
    $password = '';
    */

    try {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $database);
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (Throwable $e) {
        logAppError('DB connection failed: ' . $e->getMessage());
        http_response_code(500);
        if (isDebugMode()) {
            echo 'Database connection failed: ' . esc($e->getMessage());
            echo '<br>Check db.php credentials and imported schema.sql';
        } else {
            echo 'Application DB error. Please check data/app_error.log';
        }
        exit;
    }
}

function esc(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function formatAppDate(?string $dateValue): string
{
    if ($dateValue === null || trim($dateValue) === '') {
        return '-';
    }

    try {
        $date = new DateTime($dateValue);
        return $date->format('d, M, Y');
    } catch (Throwable $e) {
        return (string) $dateValue;
    }
}
