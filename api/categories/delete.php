<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

mysqli_report(MYSQLI_REPORT_OFF);

$category_id = $_GET['categoryId'] ?? 0;

if (!$category_id) {
    http_response_code(400);
    echo json_encode(["message" => "Category ID required"]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
$stmt->bind_param("i", $category_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(["message" => "Category not found"]);
        exit;
    }

    echo json_encode(["message" => "Category deleted successfully"]);
} else {
    http_response_code(409);
    echo json_encode(["message" => "Cannot delete category because it has products linked to it"]);
}