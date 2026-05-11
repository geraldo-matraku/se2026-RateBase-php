<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

$category_id = $_GET['categoryId'] ?? 0;
$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'] ?? '';

if (!$category_id || !$name) {
    http_response_code(400);
    echo json_encode(["message" => "Category ID and name are required"]);
    exit;
}

$stmt = $conn->prepare("UPDATE categories SET name = ? WHERE category_id = ?");
$stmt->bind_param("si", $name, $category_id);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["message" => "Update failed"]);
    exit;
}

$stmt = $conn->prepare("SELECT category_id, name FROM categories WHERE category_id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Category not found"]);
    exit;
}

$category = $result->fetch_assoc();

echo json_encode([
    "message" => "Category updated successfully",
    "category" => $category
]);