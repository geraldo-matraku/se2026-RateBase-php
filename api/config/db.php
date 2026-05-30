<?php

$host = getenv("DB_HOST") ?: getenv("MYSQLHOST");
$user = getenv("DB_USER") ?: getenv("MYSQLUSER");
$password = getenv("DB_PASSWORD") ?: getenv("MYSQLPASSWORD");
$database = getenv("DB_NAME") ?: getenv("MYSQLDATABASE");
$port = getenv("DB_PORT") ?: getenv("MYSQLPORT") ?: 3306;

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