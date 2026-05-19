<?php
include __DIR__ . "/../config/db.php";
include __DIR__ . "/../config/session.php";

header("Content-Type: application/json");

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

$category_id = $_GET['categoryId'] ?? 0;

if (!$category_id) {
    http_response_code(400);
    echo json_encode(["message" => "Category ID is missing"]);
    exit;
}

$checkStmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
$checkStmt->bind_param("i", $category_id);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Category not found"]);
    exit;
}

$currentCategory = $result->fetch_assoc();
$checkStmt->close();

$name        = $_POST['name']        ?? $currentCategory['name'];
$description = $_POST['description'] ?? $currentCategory['description'];
$imageName   = $currentCategory['image'];

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

    $target_dir = __DIR__ . "/../../uploads/";

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $new_file_name  = "cat_" . uniqid() . "." . $file_extension;
    $target_file    = $target_dir . $new_file_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {

        if (!empty($currentCategory['image'])) {
            $oldImagePath = $target_dir . $currentCategory['image'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        $imageName = $new_file_name;

        $updateProducts = $conn->prepare("
            UPDATE products 
            SET image = ? 
            WHERE category_id = ? 
            AND (image = ? OR image IS NULL)
        ");
        $updateProducts->bind_param(
            "sis",
            $imageName,
            $category_id,
            $currentCategory['image']
        );
        $updateProducts->execute();
        $updateProducts->close();

    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to upload image"]);
        exit;
    }
}

$updateStmt = $conn->prepare("
    UPDATE categories 
    SET name = ?, description = ?, image = ?
    WHERE category_id = ?
");
$updateStmt->bind_param("sssi", $name, $description, $imageName, $category_id);

if ($updateStmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Category updated successfully",
        "data"    => [
            "category_id" => $category_id,
            "name"        => $name,
            "description" => $description,
            "image"       => $imageName
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Database update failed"]);
}