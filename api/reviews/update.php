<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$review_id = $_GET['reviewid'] ?? null;

if (!$review_id) {
    http_response_code(400);
    echo json_encode(["message" => "Review ID required"]);
    exit;
}

$checkStmt = $conn->prepare("SELECT user_id FROM reviews WHERE review_id = ?");
$checkStmt->bind_param("i", $review_id);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Review not found"]);
    exit;
}

$review = $result->fetch_assoc();
$isAdmin = $_SESSION['role'] === 'admin';
$isOwner = $_SESSION['user_id'] === $review['user_id'];

if (!$isAdmin && !$isOwner) {
    http_response_code(403);
    echo json_encode(["message" => "Nuk keni leje per te edituar kete review"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$rating  = $data['rating']  ?? null;
$comment = $data['comment'] ?? null;

if (!$rating || !$comment) {
    http_response_code(400);
    echo json_encode(["message" => "Rating dhe komenti jane te detyrueshme"]);
    exit;
}

if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(["message" => "Rating duhet te jete mes 1 dhe 5"]);
    exit;
}

$stmt = $conn->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE review_id = ?");
$stmt->bind_param("isi", $rating, $comment, $review_id);

if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Review updated successfully",
        "data"    => [
            "review_id" => $review_id,
            "rating"    => $rating,
            "comment"   => $comment
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Update failed"]);
}