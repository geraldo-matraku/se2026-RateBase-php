<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$productId   = $_GET['product_id'] ?? null;
$currentUser = $_SESSION['user_id'];

if (!$productId || !is_numeric($productId)) {
    http_response_code(400);
    echo json_encode([
        "status"  => "error",
        "message" => "Product ID must be valid"
    ]);
    exit;
}

try {
    $sql = "
        SELECT 
            r.*,
            u.first_name,
            u.last_name,
            COALESCE(SUM(v.vote_type = 'up'),   0) AS up_count,
            COALESCE(SUM(v.vote_type = 'down'), 0) AS down_count,
            MAX(CASE WHEN v.user_id = ? THEN v.vote_type END) AS my_vote
        FROM reviews r
        INNER JOIN users u ON r.user_id = u.user_id
        LEFT JOIN review_votes v ON r.review_id = v.review_id
        WHERE r.product_id = ?
        GROUP BY r.review_id
        ORDER BY r.created_at DESC  
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("SQL error: " . $conn->error);
    }

    $stmt->bind_param("ii", $currentUser, $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    $reviews = [];

    while ($row = $result->fetch_assoc()) {
        $row['up_count']   = (int) $row['up_count'];
        $row['down_count'] = (int) $row['down_count'];
        $row['my_vote']    = $row['my_vote'] ?? null;
        $reviews[]         = $row;
    }

    echo json_encode([
        "status"        => "success",
        "product_id"    => $productId,
        "total_reviews" => count($reviews),
        "data"          => $reviews
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => $e->getMessage()
    ]);
}