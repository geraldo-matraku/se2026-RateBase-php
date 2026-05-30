<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../");
$dotenv->load();

$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "DB connection failed"]);
    exit;
}
?>

