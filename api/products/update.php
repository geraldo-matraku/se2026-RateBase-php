<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

$product_id = $_GET['productId'] ?? 0;
$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'] ?? '';
$description = $data['description'] ?? '';
$category_id = $data['category_id'] ?? 0;

if (!$product_id || !$name || !$description || !$category_id) {
    http_response_code(400);
    echo json_encode(["message" => "All fields are required"]);
    exit;
}

$stmt = $conn->prepare("
    UPDATE products 
    SET name = ?, description = ?, category_id = ?
    WHERE product_id = ?
");

$stmt->bind_param("ssii", $name, $description, $category_id, $product_id);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["message" => "Update failed"]);
    exit;
}

if ($stmt->affected_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Product not found or no changes made"]);
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
$product = $result->fetch_assoc();

echo json_encode([
    "message" => "Product updated successfully",
    "product" => $product
]);