<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "message" => "Unauthorized"
    ]);
    exit;
}

if($_SESSION['role'] !== 'admin'){
    http_response_code(403);
     echo json_encode([
    "message" => "Admin Only"
]);
exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'] ?? '';
$description = $data['description'] ?? '';
$category_id = $data['category_id'] ?? 0;

if (!$name || !$description || !$category_id) {
    http_response_code(400);
    echo json_encode([
        "message" => "All fields are required"
    ]);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO products (name, description, category_id)
    VALUES (?, ?, ?)
");

$stmt->bind_param("ssi", $name, $description, $category_id);

if ($stmt->execute()) {
    $product_id = $stmt->insert_id;

    http_response_code(201);
    echo json_encode([
        "message" => "Product created successfully",
        "product" => [
            "product_id" => $product_id,
            "name" => $name,
            "description" => $description,
            "category_id" => (int)$category_id
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "message" => "Failed to create product"
    ]);
}