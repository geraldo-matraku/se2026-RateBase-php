<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";
include __DIR__ . "/../email/sendPaymentEmail.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$payment_id = isset($data["payment_id"]) ? (int) $data["payment_id"] : 0;
$user_id = (int) $_SESSION["user_id"];

if ($payment_id <= 0) {
    http_response_code(400);
    echo json_encode(["message" => "payment_id is required"]);
    exit;
}


$stmt = $conn->prepare("
    SELECT 
        p.payment_id,
        p.amount,
        p.currency,
        p.status,
        u.first_name,
        u.last_name,
        u.email
    FROM payments p
    JOIN users u ON p.user_id = u.user_id
    WHERE p.payment_id = ? AND p.user_id = ?
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "message" => "Failed to prepare select statement",
        "error" => $conn->error
    ]);
    exit;
}

$stmt->bind_param("ii", $payment_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();
$paymentData = $result->fetch_assoc();

if (!$paymentData) {
    http_response_code(404);
    echo json_encode(["message" => "Payment not found"]);
    exit;
}

if ($paymentData["status"] === "completed") {
    echo json_encode([
        "message" => "Payment already completed",
        "payment_id" => (int) $payment_id,
        "email_sent" => false
    ]);
    exit;
}


$paddle_payment_id = "PADDLE_TEST_COMPLETED_" . $payment_id;

$stmt = $conn->prepare("
    UPDATE payments
    SET 
        status = 'completed',
        paddle_payment_id = ?
    WHERE payment_id = ? AND user_id = ?
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "message" => "Failed to prepare update statement",
        "error" => $conn->error
    ]);
    exit;
}

$stmt->bind_param("sii", $paddle_payment_id, $payment_id, $user_id);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        "message" => "Failed to complete payment",
        "error" => $stmt->error
    ]);
    exit;
}

$email_sent = false;

if (function_exists("sendPaymentEmail")) {
    $email_sent = sendPaymentEmail(
        $paymentData["email"],
        $paymentData["first_name"] . " " . $paymentData["last_name"],
        $paymentData["amount"],
        $paymentData["currency"],
        $payment_id
    );
}

echo json_encode([
    "message" => "Paddle payment completed successfully",
    "payment_id" => (int) $payment_id,
    "paddle_payment_id" => $paddle_payment_id,
    "email_sent" => $email_sent
]);