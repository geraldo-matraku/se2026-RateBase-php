<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

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

$product_id = $_GET['productId'] ?? 0;

if (!$product_id) {
    http_response_code(400);
    echo json_encode(["message" => "Product ID is required"]);
    exit;
}

$name        = $_POST['name']        ?? null;
$description = $_POST['description'] ?? null;
$category_id = $_POST['category_id'] ?? null;

// Trajto imazhin nese vjen
$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . "/../uploads/";
    $ext        = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename   = uniqid('product_', true) . '.' . $ext;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
        $image_path = $filename;
    }
}

$fields = [];
$params = [];
$types  = '';

if ($name !== null) {
    $fields[] = "name = ?";
    $params[] = $name;
    $types   .= 's';
}

if ($description !== null) {
    $fields[] = "description = ?";
    $params[] = $description;
    $types   .= 's';
}

if ($category_id !== null) {
    $fields[] = "category_id = ?";
    $params[] = (int) $category_id;
    $types   .= 'i';
}

if ($image_path !== null) {
    $fields[] = "image = ?";
    $params[] = $image_path;
    $types   .= 's';
}

if (empty($fields)) {
    http_response_code(400);
    echo json_encode(["message" => "Nuk u dergua asnje fushe per update"]);
    exit;
}

$params[] = (int) $product_id;
$types   .= 'i';

$sql  = "UPDATE products SET " . implode(", ", $fields) . " WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["message" => "Update failed"]);
    exit;
}

$stmt = $conn->prepare("
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

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result  = $stmt->get_result();
$product = $result->fetch_assoc();

echo json_encode([
    "message" => "Product updated successfully",
    "product" => $product
]);