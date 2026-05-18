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

$category_id = $_GET['categoryId'] ?? 0;

if (!$category_id) {
    http_response_code(400);
    echo json_encode(["message" => "Category ID required"]);
    exit;
}

$checkStmt = $conn->prepare("SELECT image FROM categories WHERE category_id = ?");
$checkStmt->bind_param("i", $category_id);
$checkStmt->execute();
$category = $checkStmt->get_result()->fetch_assoc();
$checkStmt->close();

if (!$category) {
    http_response_code(404);
    echo json_encode(["message" => "Category not found"]);
    exit;
}

$getProducts = $conn->prepare("SELECT product_id, image FROM products WHERE category_id = ?");
$getProducts->bind_param("i", $category_id);
$getProducts->execute();
$products = $getProducts->get_result()->fetch_all(MYSQLI_ASSOC);
$getProducts->close();

$conn->begin_transaction();

try {
    foreach ($products as $product) {
        $pid = $product['product_id'];

        $s1 = $conn->prepare("
            DELETE rv FROM review_votes rv
            INNER JOIN reviews r ON rv.review_id = r.review_id
            WHERE r.product_id = ?
        ");
        $s1->bind_param("i", $pid);
        $s1->execute();
        $s1->close();

        $s2 = $conn->prepare("DELETE FROM reviews WHERE product_id = ?");
        $s2->bind_param("i", $pid);
        $s2->execute();
        $s2->close();

        $s3 = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $s3->bind_param("i", $pid);
        $s3->execute();
        $s3->close();
    }

    $s4 = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $s4->bind_param("i", $category_id);
    $s4->execute();
    $s4->close();

    $conn->commit();

    $upload_dir = "../uploads/";

    if (!empty($category['image']) && file_exists($upload_dir . $category['image'])) {
        unlink($upload_dir . $category['image']);
    }

    foreach ($products as $product) {
        if (!empty($product['image']) && file_exists($upload_dir . $product['image'])) {
            unlink($upload_dir . $product['image']);
        }
    }

    echo json_encode(["message" => "Category deleted successfully"]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        "message" => "Delete failed",
        "error"   => $e->getMessage()
    ]);
}