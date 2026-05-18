<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["message" => "Admin Only"]);
    exit;
}

$product_id = $_GET['productId'] ?? 0;

if (!$product_id) {
    http_response_code(400);
    echo json_encode(["message" => "Product ID is required"]);
    exit;
}

$checkStmt = $conn->prepare("
    SELECT * FROM products 
    WHERE product_id = ?
");
$checkStmt->bind_param("i", $product_id);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Product not found"]);
    exit;
}

$currentProduct = $result->fetch_assoc();

$name        = $_POST['name']        ?? $currentProduct['name'];
$description = $_POST['description'] ?? $currentProduct['description'];
$category_id = $_POST['category_id'] ?? $currentProduct['category_id'];
$imageName   = $currentProduct['image']; 

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

    $target_dir    = "../uploads/";
    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $new_file_name  = "product_" . uniqid() . "." . $file_extension;
    $target_file    = $target_dir . $new_file_name;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {

        // Fshi foton e vjetër nëse ekziston
        if (!empty($currentProduct['image'])) {
            $oldImagePath = $target_dir . $currentProduct['image'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        $imageName = $new_file_name;

    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to upload image"]);
        exit;
    }
}

$updateStmt = $conn->prepare("
    UPDATE products
    SET
        name        = ?,
        description = ?,
        category_id = ?,
        image       = ?
    WHERE product_id = ?
");

$updateStmt->bind_param(
    "ssisi",
    $name,
    $description,
    $category_id,
    $imageName,
    $product_id
);

if ($updateStmt->execute()) {

    $selectStmt = $conn->prepare("
        SELECT
            p.product_id,
            p.name,
            p.description,
            p.category_id,
            p.image,
            c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.product_id = ?
    ");
    $selectStmt->bind_param("i", $product_id);
    $selectStmt->execute();
    $product = $selectStmt->get_result()->fetch_assoc();

    echo json_encode([
        "status"  => "success",
        "message" => "Product updated successfully",
        "data"    => $product
    ]);

} else {
    http_response_code(500);
    echo json_encode(["message" => "Database update failed"]);
}