<?php
header("Content-Type: application/json; charset=UTF-8");

include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";
include __DIR__ . "/../config/cors.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $sql = "
        SELECT
            r.review_id,
            r.product_id,
            r.rating,
            r.comment,
            r.image,
            r.created_at,
            p.name AS product_name,
            p.category_id,
            c.name AS category_name
        FROM reviews r
        JOIN products p ON r.product_id = p.product_id
        JOIN categories c ON p.category_id = c.category_id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("SQL Prepare Error: " . $conn->error);
    }

    $stmt->bind_param("i", $userId);

    if (!$stmt->execute()) {
        throw new Exception("Execute failed");
    }

    $result = $stmt->get_result();
    $reviews = [];

    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "user_id" => $userId,
        "total_reviews" => count($reviews),
        "data" => $reviews
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}