<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_error.log');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'note_platform');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$checkDb = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
if ($checkDb->num_rows === 0) {
    $conn->query("CREATE DATABASE " . DB_NAME);
    $conn->select_db(DB_NAME);

    $sqlFile = __DIR__ . '/../database.sql';
    $sqlContent = file_get_contents($sqlFile);

    $lines = explode("\n", $sqlContent);
    $cleanLines = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || strpos($trimmed, '--') === 0) {
            continue;
        }
        $cleanLines[] = $line;
    }
    $sqlContent = implode("\n", $cleanLines);
    $sqlContent = str_ireplace('CREATE DATABASE IF NOT EXISTS note_platform;', '', $sqlContent);
    $sqlContent = str_ireplace('USE note_platform;', '', $sqlContent);

    $statements = array_filter(array_map('trim', explode(';', $sqlContent)));

    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $conn->query($stmt);
        }
    }
} else {
    $conn->select_db(DB_NAME);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$siteName = "StudyMate";

$scriptDir = dirname($_SERVER['PHP_SELF']);
$projectRoot = '/t-project';
if (strpos($scriptDir, $projectRoot) === 0) {
    $relative = substr($scriptDir, strlen($projectRoot));
    $depth = substr_count($relative, '/');
    $basePath = str_repeat('../', $depth);
} else {
    $basePath = '';
}
