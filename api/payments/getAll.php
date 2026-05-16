<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

$result = $conn->query("
    SELECT 
        p.payment_id,
        p.user_id,
        u.first_name,
        u.last_name,
        u.email,
        p.amount,
        p.currency,
        p.description,
        p.status,
        p.created_at
    FROM payments p
    LEFT JOIN users u ON p.user_id = u.user_id
    ORDER BY p.created_at DESC
");

$payments = [];

while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}

echo json_encode([
    "message" => "Payments fetched successfully",
    "payments" => $payments
]);