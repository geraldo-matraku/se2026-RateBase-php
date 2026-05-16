<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

$stats = [];

$res = $conn->query("SELECT COUNT(*) as total FROM products");
$stats['total_products'] = $res->fetch_assoc()['total'];

$res = $conn->query("SELECT COUNT(*) as total FROM categories");
$stats['total_categories'] = $res->fetch_assoc()['total'];

$res = $conn->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $res->fetch_assoc()['total'];

$res = $conn->query("SELECT COUNT(*) as total FROM reviews");
$stats['total_reviews'] = $res->fetch_assoc()['total'];

$res = $conn->query("SELECT ROUND(AVG(rating), 1) as average FROM reviews");
$stats['average_rating'] = $res->fetch_assoc()['average'] ?? 0;

echo json_encode([
    "status" => "success",
    "data" => $stats
]);