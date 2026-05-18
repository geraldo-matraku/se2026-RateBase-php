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
    echo json_encode(["message" => "Product ID required"]);
    exit;
}

$checkStmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
$checkStmt->bind_param("i", $product_id);
$checkStmt->execute();
$product = $checkStmt->get_result()->fetch_assoc();
$checkStmt->close();

if (!$product) {
    http_response_code(404);
    echo json_encode(["message" => "Product not found"]);
    exit;
}

$conn->begin_transaction();

try {
    $s1 = $conn->prepare("
        DELETE rv FROM review_votes rv
        INNER JOIN reviews r ON rv.review_id = r.review_id
        WHERE r.product_id = ?
    ");
    $s1->bind_param("i", $product_id);
    $s1->execute();
    $s1->close();

    $s2 = $conn->prepare("DELETE FROM reviews WHERE product_id = ?");
    $s2->bind_param("i", $product_id);
    $s2->execute();
    $s2->close();


    $s4 = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $s4->bind_param("i", $product_id);
    $s4->execute();
    $s4->close();

    $conn->commit();

    if (!empty($product['image'])) {
        $imagePath = "../uploads/" . $product['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    echo json_encode(["message" => "Product deleted successfully"]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        "message" => "Delete failed",
        "error"   => $e->getMessage() // shih gabimin konkret
    ]);
}