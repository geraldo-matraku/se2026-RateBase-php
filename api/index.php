<?php
include "config/cors.php";

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

/* heq query string */
$uri = strtok($uri, '?');

/* heq base folder */
$uri = str_replace("/sistem-vleresimi-produktesh-php/api", "", $uri);

/* normalizim */
$uri = rtrim($uri, "/");

if ($uri == "" || $uri == "/") {
    echo json_encode([
        "status" => "API RUNNING"
    ]);
    exit;
}

switch ($uri) {

    case '/auth/login':
        require "auth/login.php";
        break;

    case '/auth/register':
        require "auth/register.php";
        break;

    case '/auth/logout':
        require "auth/logout.php";
        break;

    case '/users/me':
        require "users/me.php";
        break;

    default:
        http_response_code(404);
        echo json_encode([
            "message" => "Route not found",
            "received_path" => $uri
        ]);
        break;
}