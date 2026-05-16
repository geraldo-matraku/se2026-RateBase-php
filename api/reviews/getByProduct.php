<?php
include __DIR__ . "/../config/cors.php";
header("Content-Type: application/json; charset=UTF-8");
include __DIR__ . "/../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

$productId = $_GET['product_id'] ?? null;

if (!$productId || !is_numeric($productId)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Product ID must be valid"
    ]);
    exit;
}

try {
    $sql = "
        SELECT r.*, u.first_name, u.last_name 
        FROM reviews r
        INNER JOIN users u ON r.user_id = u.user_id
        WHERE r.product_id = ?
        ORDER BY r.created_at DESC
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("SQL error: " . $conn->error);
    }

    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    $reviews = [];

    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "product_id" => $productId,
        "total_reviews" => count($reviews),
        "data" => $reviews
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}