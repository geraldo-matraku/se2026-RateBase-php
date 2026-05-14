<?php
include "config/cors.php";

header("Content-Type: application/json");


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

// Dynamic product routes:
// GET /products/{id}
// PUT /products/{id}
// DELETE /products/{id}
if (preg_match('/^\/products\/(\d+)$/', $uri, $matches)) {
    $_GET['productId'] = $matches[1];

    if ($method === "GET") {
        require 'products/getById.php';
        exit;
    }

    if ($method === "PUT" || $method === "POST") {
        require 'products/update.php';
        exit;
    }

    if ($method === "DELETE") {
        require 'products/delete.php';
        exit;
    }

    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit;
}

// Dynamic category routes:
// GET /categories/{id}
// PUT /categories/{id}
// DELETE /categories/{id}
if (preg_match('/^\/categories\/(\d+)$/', $uri, $matches)) {
    $_GET['categoryId'] = $matches[1];

    if ($method === "GET") {
        require 'categories/getOne.php';
        exit;
    }

    if ($method === "PUT" || $method === "POST") {
        require 'categories/update.php';
        exit;
    }

    if ($method === "DELETE") {
        require 'categories/delete.php';
        exit;
    }

    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
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

    case '/categories':
    case '/categories/getAll':
        require 'categories/getAll.php';
        break;

    case '/products':
    case '/products/getAll':
        require "products/getAll.php";
        break;

    case '/products/create':
        if ($method !== "POST") {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Method not allowed"]);
            break;
        }
        require "products/create.php";
        break;

    case '/categories/create':
        if ($method !== "POST") {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Method not allowed"]);
            break;
        }
        require "categories/create.php";
        break;

    case '/stats':
        require "stats/getStats.php";
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