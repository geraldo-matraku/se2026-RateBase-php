<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

$product_id = $_GET['productId'] ?? 0;

if (!$product_id) {
    http_response_code(400);
    echo json_encode(["message" => "Product ID is required"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        p.product_id,
        p.name,
        p.description,
        p.category_id,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE p.product_id = ?
");

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Product not found"]);
    exit;
}

$product = $result->fetch_assoc();

echo json_encode([
    "message" => "Product fetched successfully",
    "product" => $product
]);