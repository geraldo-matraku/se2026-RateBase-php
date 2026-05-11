<?php
include __DIR__ . "/../config/db.php";
header("Content-Type: application/json");

// 1. Marrim ID-në nga URL (e kalon Router-i te $_GET)
$category_id = $_GET['categoryId'] ?? 0;

if (!$category_id) {
    http_response_code(400);
    echo json_encode(["message" => "Category ID is missing"]);
    exit;
}

// 2. Kontrollojmë nëse ka ardhur skedari i fotos
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
    http_response_code(400);
    echo json_encode(["message" => "No image file uploaded"]);
    exit;
}

// 3. Logjika e ruajtjes së skedarit fizik
$target_dir = "../uploads/";
$file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
$new_file_name = "cat_" . uniqid() . "." . $file_extension;
$target_file = $target_dir . $new_file_name;

if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
    
    // 4. Update vetëm kolonën 'image' në DB për këtë ID
    $stmt = $conn->prepare("UPDATE categories SET image = ? WHERE category_id = ?");
    $stmt->bind_param("si", $new_file_name, $category_id);

    if ($stmt->execute()) {
        echo json_encode([
            "message" => "Image updated successfully",
            "image" => $new_file_name
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Database update failed"]);
    }
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to save image to folder"]);
}