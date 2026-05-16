<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$category_id = $_GET['categoryId'] ?? null;
$query = $_GET['q'] ?? '';

if (!$category_id) {
    http_response_code(400);
    echo json_encode(["message" => "Category ID required"]);
    exit;
}

$search = '%' . $query . '%';

$stmt = $conn->prepare("
    SELECT 
        p.product_id,
        p.name,
        p.description,
        p.image,
        p.created_at,
        p.category_id,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE p.category_id = ? AND (p.name LIKE ? OR p.description LIKE ?)
    ORDER BY p.created_at DESC
");

$stmt->bind_param("iss", $category_id, $search, $search);
$stmt->execute();

$result = $stmt->get_result();
$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $products,
    "total" => count($products)
]);