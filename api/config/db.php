<?php
$host = isset($_ENV['DB_HOST']) ? trim($_ENV['DB_HOST']) : '';
$user = isset($_ENV['DB_USER']) ? trim($_ENV['DB_USER']) : '';
$pass = isset($_ENV['DB_PASS']) ? trim($_ENV['DB_PASS']) : '';
$name = isset($_ENV['DB_NAME']) ? trim($_ENV['DB_NAME']) : '';
$port = isset($_ENV['DB_PORT']) ? intval(trim($_ENV['DB_PORT'])) : 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $name, $port);
} catch (Exception $e) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Gabim gjatë lidhjes me databazën:\n";
    echo $e->getMessage();
    exit;
}