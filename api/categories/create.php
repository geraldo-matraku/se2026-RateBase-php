<?php
include __DIR__ . "/../config/db.php";
include __DIR__ . "/../config/session.php";
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

if($_SESSION['role'] !== 'admin'){
    http_response_code(403);
     echo json_encode([
    "message" => "Admin Only"
]);
exit;
}

$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';

if (!$name) {
    http_response_code(400);
    echo json_encode(["message" => "Emri i kategorisë është i detyrueshëm"]);
    exit;
}

$image_name = null; 


if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    
    $target_dir = "../uploads/"; 
    
    $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    
    $new_file_name = "cat_" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . $new_file_name;

    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($file_extension, $allowed_types)) {
        http_response_code(400);
        echo json_encode(["message" => "Vetëm formatet JPG, PNG, GIF, WEBP lejohen."]);
        exit;
    }

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_name = $new_file_name; 
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Gabim gjatë ruajtjes së imazhit në server."]);
        exit;
    }
}

$stmt = $conn->prepare("INSERT INTO categories (name, description, image) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $description, $image_name);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode([
        "message" => "Kategoria u krijua me sukses",
        "category" => [
            "id" => $conn->insert_id,
            "name" => $name,
            "description" => $description,
            "image" => $image_name
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Gabim në databazë: " . $conn->error]);
}

$stmt->close();
$conn->close();