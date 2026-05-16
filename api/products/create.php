<?php

include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);

    echo json_encode([
        "message" => "Unauthorized"
    ]);
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);

    echo json_encode([
        "message" => "Admin Only"
    ]);
    exit;
}

$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$category_id = $_POST['category_id'] ?? 0;

if (!$name || !$description || !$category_id) {
    http_response_code(400);

    echo json_encode([
        "message" => "Name, description and category_id are required"
    ]);
    exit;
}



$imageName = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

    $target_dir = "../uploads/";

    $file_extension = strtolower(
        pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION)
    );

    $imageName = "product_" . uniqid() . "." . $file_extension;

    $target_file = $target_dir . $imageName;

    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {

        http_response_code(500);

        echo json_encode([
            "message" => "Failed to upload image"
        ]);
        exit;
    }
}



$stmt = $conn->prepare("
    INSERT INTO products (
        name,
        description,
        category_id,
        image
    )
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param(
    "ssis",
    $name,
    $description,
    $category_id,
    $imageName
);

if ($stmt->execute()) {

    $product_id = $stmt->insert_id;


    $getStmt = $conn->prepare("
        SELECT 
            product_id,
            name,
            description,
            category_id,
            image
        FROM products
        WHERE product_id = ?
    ");

    $getStmt->bind_param("i", $product_id);
    $getStmt->execute();

    $result = $getStmt->get_result();
    $product = $result->fetch_assoc();

    http_response_code(201);

    echo json_encode([
        "status" => "success",
        "message" => "Product created successfully",
        "product" => $product
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        "message" => "Failed to create product"
    ]);
}