<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'] ?? '';

if (!$name) {
    http_response_code(400);
    echo json_encode(["message" => "Name is required"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
$stmt->bind_param("s", $name);

if ($stmt->execute()) {
    $category_id = $stmt->insert_id;

    http_response_code(201);
    echo json_encode([
        "message" => "Category created successfully",
        "category" => [
            "category_id" => $category_id,
            "name" => $name
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Create failed"]);
}