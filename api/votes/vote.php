<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$data      = json_decode(file_get_contents("php://input"), true);
$review_id = $data['review_id'] ?? null;
$type      = $data['type']      ?? null;
$user_id   = $_SESSION['user_id'];

if (!$review_id || !in_array($type, ['up', 'down'])) {
    http_response_code(400);
    echo json_encode(["message" => "review_id dhe type (up/down) jane te detyrueshme"]);
    exit;
}

$checkStmt = $conn->prepare("SELECT vote_id, vote_type FROM review_votes WHERE review_id = ? AND user_id = ?");
$checkStmt->bind_param("ii", $review_id, $user_id);
$checkStmt->execute();
$result   = $checkStmt->get_result();
$existing = $result->fetch_assoc();

if ($existing) {
    if ($existing['vote_type'] === $type) {
        $deleteStmt = $conn->prepare("DELETE FROM review_votes WHERE vote_id = ?");
        $deleteStmt->bind_param("i", $existing['vote_id']);
        $deleteStmt->execute();
        $action = 'removed';
    } else {
        $updateStmt = $conn->prepare("UPDATE review_votes SET vote_type = ? WHERE vote_id = ?");
        $updateStmt->bind_param("si", $type, $existing['vote_id']);
        $updateStmt->execute();
        $action = 'changed';
    }
} else {
    $insertStmt = $conn->prepare("INSERT INTO review_votes (review_id, user_id, vote_type) VALUES (?, ?, ?)");
    $insertStmt->bind_param("iis", $review_id, $user_id, $type);
    $insertStmt->execute();
    $action = 'added';
}

$countStmt = $conn->prepare("
    SELECT 
        SUM(vote_type = 'up')   AS up_count,
        SUM(vote_type = 'down') AS down_count
    FROM review_votes 
    WHERE review_id = ?
");
$countStmt->bind_param("i", $review_id);
$countStmt->execute();
$counts = $countStmt->get_result()->fetch_assoc();

echo json_encode([
    "status"     => "success",
    "action"     => $action,
    "review_id"  => $review_id,
    "up_count"   => (int)($counts['up_count']   ?? 0),
    "down_count" => (int)($counts['down_count'] ?? 0),
]);