<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = getenv("DB_HOST");
$user = getenv("DB_USER");
$password = getenv("DB_PASSWORD");
$database = getenv("DB_NAME");
$port = getenv("DB_PORT");

header("Content-Type: application/json");

try {
    $conn = mysqli_init();
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

    $conn->real_connect(
        $host,
        $user,
        $password,
        $database,
        (int) $port
    );

    $conn->set_charset("utf8mb4");
} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed",
        "details" => $e->getMessage(),
        "env_check" => [
            "host" => $host ?: "missing",
            "user" => $user ?: "missing",
            "database" => $database ?: "missing",
            "port" => $port ?: "missing"
        ]
    ]);

    exit;
}