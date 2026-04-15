<?php
header("Content-Type: application/json; charset=UTF-8");
include __DIR__ . "/../config/db.php";

$categoryId = isset($_GET['categoryId']) ? intval($_GET['categoryId']) : 0;

if ($categoryId <= 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "error", 
        "message" => "ID e kategorisë duhet të jetë një numër i vlefshëm."
    ]);
    exit;
}

try {
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            INNER JOIN categories c ON p.category_id = c.category_id 
            WHERE p.category_id = ?";

    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Gabim në përgatitjen e SQL: " . $conn->error);
    }

    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "requested_category_id" => $categoryId,
        "total_results" => count($products),
        "data" => $products
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}