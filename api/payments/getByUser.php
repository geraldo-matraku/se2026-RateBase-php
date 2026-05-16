<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

$user_id = $_GET["userId"] ?? 0;

if (!$user_id) {
    http_response_code(400);
    echo json_encode(["message" => "User ID is required"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        payment_id,
        user_id,
        amount,
        currency,
        description,
        status,
        created_at
    FROM payments
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$payments = [];

while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}

echo json_encode([
    "message" => "User payments fetched successfully",
    "payments" => $payments
]);