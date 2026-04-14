<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "message" => "Unauthorized"
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT user_id, first_name, last_name, email, role 
    FROM users 
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    http_response_code(404);
    echo json_encode([
        "message" => "User not found"
    ]);
    exit;
}

http_response_code(200);
echo json_encode([
    "user" => $user
]);