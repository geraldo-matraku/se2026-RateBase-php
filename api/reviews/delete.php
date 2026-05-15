<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json; charset=UTF-8");
mysqli_report(MYSQLI_REPORT_OFF);

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
    echo json_encode(["message" => "Nuk keni leje per te fshire kete review"]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
$stmt->bind_param("i", $review_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(["message" => "Review not found"]);
        exit;
    }

    echo json_encode(["message" => "Review deleted successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Delete failed"]);
}