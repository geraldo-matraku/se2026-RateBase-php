<?php
include "config/cors.php";

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$uri = strtok($uri, '?');
$base_path = "/sistem-vleresimi-produktesh-php/api";
$uri = str_replace($base_path, "", $uri);
$uri = rtrim($uri, "/");

if ($uri == "" || $uri == "/") {
    header("Content-Type: application/json");
    echo json_encode([
        "status" => "API RUNNING",
        "version" => "1.0.0"
    ]);
    exit;
}

if (preg_match('/^\/products\/getByCategory\/(\d+)$/', $uri, $matches)) {
    $_GET['categoryId'] = $matches[1]; 
    require 'products/getByCategory.php';
    exit;
}

if (preg_match('/^\/categories\/(\d+)$/', $uri, $matches)) {
    $_GET['categoryId'] = $matches[1];
    require 'categories/getOne.php';
    exit;
}

header("Content-Type: application/json");

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

    case '/categories':
    case '/categories/getAll':
        require 'categories/getAll.php';
        break;

    default:
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Route not found",
            "received_path" => $uri
        ]);
        break;
}