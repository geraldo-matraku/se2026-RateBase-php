<?php

$host = getenv("APP_DB_HOST") ?: getenv("MYSQLHOST");
$user = getenv("APP_DB_USER") ?: getenv("MYSQLUSER");
$password = getenv("APP_DB_PASSWORD") ?: getenv("MYSQLPASSWORD");
$database = getenv("APP_DB_NAME") ?: getenv("MYSQLDATABASE");
$port = getenv("APP_DB_PORT") ?: getenv("MYSQLPORT") ?: 3306;

$conn = mysqli_init();

$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

if (!$conn->real_connect($host, $user, $password, $database, (int)$port)) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Gabim gjatë lidhjes me databazën",
        "details" => $conn->connect_error,
        "env_check" => [
            "host" => $host ? "exists" : "missing",
            "user" => $user ? "exists" : "missing",
            "database" => $database ? "exists" : "missing",
            "port" => $port ?: "missing"
        ]
    ]);
    exit;
}

$conn->set_charset("utf8mb4");