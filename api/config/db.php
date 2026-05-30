<?php

mysqli_report(MYSQLI_REPORT_OFF);

$host = getenv("APP_DB_HOST");
$user = getenv("APP_DB_USER");
$password = getenv("APP_DB_PASSWORD");
$database = getenv("APP_DB_NAME");
$port = getenv("APP_DB_PORT");

header("Content-Type: application/json");

$conn = mysqli_init();

if (!$conn) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Could not initialize database connection"
    ]);
    exit;
}

$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

$connected = @$conn->real_connect(
    $host,
    $user,
    $password,
    $database,
    (int) $port
);

if (!$connected) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed",
        "details" => $conn->connect_error,
        "env_check" => [
            "host" => $host ?: "missing",
            "user" => $user ?: "missing",
            "database" => $database ?: "missing",
            "port" => $port ?: "missing"
        ]
    ]);
    exit;
}

$conn->set_charset("utf8mb4");