<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";
include __DIR__ . "/../config/paddle.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$amount = isset($data["amount"]) ? (float) $data["amount"] : 0;
$user_id = (int) $_SESSION["user_id"];

if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(["message" => "amount is required"]);
    exit;
}

$currency = PADDLE_CURRENCY;
$description = PADDLE_DESCRIPTION;


$paddle_payment_id = "PADDLE_TEST_PENDING";

$stmt = $conn->prepare("
    INSERT INTO payments (
        user_id,
        amount,
        currency,
        status,
        description,
        paddle_payment_id
    )
    VALUES (?, ?, ?, 'pending', ?, ?)
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "message" => "Failed to prepare statement",
        "error" => $conn->error
    ]);
    exit;
}

$stmt->bind_param(
    "idsss",
    $user_id,
    $amount,
    $currency,
    $description,
    $paddle_payment_id
);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        "message" => "Failed to save payment",
        "error" => $stmt->error
    ]);
    exit;
}

$payment_id = $stmt->insert_id;

echo json_encode([
    "message" => "Paddle payment created successfully",
    "payment" => [
        "payment_id" => (int) $payment_id,
        "user_id" => (int) $user_id,
        "amount" => (float) $amount,
        "currency" => $currency,
        "status" => "pending",
        "description" => $description,
        "paddle_payment_id" => $paddle_payment_id
    ],
    "paddle" => [
        "environment" => PADDLE_ENVIRONMENT,
        "price_id" => PADDLE_PRICE_ID,
        "client_token" => PADDLE_CLIENT_TOKEN
    ]
]);