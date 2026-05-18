<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json; charset=UTF-8");
mysqli_report(MYSQLI_REPORT_OFF);

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
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
$checkStmt->close();

$isAdmin = $_SESSION['role'] === 'admin';
$isOwner = (int)$_SESSION['user_id'] === (int)$review['user_id'];

if (!$isAdmin && !$isOwner) {
    http_response_code(403);
    echo json_encode(["message" => "Nuk keni leje per te fshire kete review"]);
    exit;
}

$conn->begin_transaction();

try {
    $s1 = $conn->prepare("DELETE FROM review_votes WHERE review_id = ?");
    $s1->bind_param("i", $review_id);
    $s1->execute();
    $s1->close();

    $s2 = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
    $s2->bind_param("i", $review_id);
    $s2->execute();
    $s2->close();

    $conn->commit();

    echo json_encode(["message" => "Review deleted successfully"]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        "message" => "Delete failed",
        "error"   => $e->getMessage()
    ]);
}