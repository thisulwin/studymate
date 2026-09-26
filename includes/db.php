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
    if (file_exists($sqlFile)) {
        $sqlContent = file_get_contents($sqlFile);
        if ($conn->multi_query($sqlContent)) {
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());
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
$possibleRoots = ['/studymate', '/StudyMate project', '/StudyMate-project', '/StudyMate_project', '/StudyMate', '/t-project', '/t_project'];
$basePath = '';
foreach ($possibleRoots as $pRoot) {
    if (strpos($scriptDir, $pRoot) === 0) {
        $relative = substr($scriptDir, strlen($pRoot));
        $depth = substr_count($relative, '/');
        $basePath = str_repeat('../', $depth);
        break;
    }
}
