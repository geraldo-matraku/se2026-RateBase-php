<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

mysqli_report(MYSQLI_REPORT_OFF);

$product_id = $_GET['productId'] ?? 0;

if (!$product_id) {
    http_response_code(400);
    echo json_encode(["message" => "Product ID required"]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(["message" => "Product not found"]);
        exit;
    }

    echo json_encode([
        "message" => "Product deleted successfully"
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "message" => "Delete failed"
    ]);
}