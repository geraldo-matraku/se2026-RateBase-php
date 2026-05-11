<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

$stats = [];

$res = $conn->query("SELECT COUNT(*) as total FROM products");
$stats['total_products'] = $res->fetch_assoc()['total'];

$res = $conn->query("SELECT COUNT(*) as total FROM categories");
$stats['total_categories'] = $res->fetch_assoc()['total'];

$res = $conn->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $res->fetch_assoc()['total'];



echo json_encode([
    "status" => "success",
    "data" => $stats
]);