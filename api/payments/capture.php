<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";
include __DIR__ . "/../config/paypal.php";
include __DIR__ . "/../email/sendPaymentEmail.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$payment_id = $data["payment_id"] ?? 0;
$paypal_order_id = $data["paypal_order_id"] ?? "";

if (!$payment_id || !$paypal_order_id) {
    http_response_code(400);
    echo json_encode(["message" => "payment_id and paypal_order_id are required"]);
    exit;
}

function getPayPalAccessToken() {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . "/v1/oauth2/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ":" . PAYPAL_CLIENT_SECRET);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/json",
        "Accept-Language: en_US"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result["access_token"] ?? null;
}

$accessToken = getPayPalAccessToken();

if (!$accessToken) {
    http_response_code(500);
    echo json_encode(["message" => "Failed to get PayPal access token"]);
    exit;
}

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . "/v2/checkout/orders/" . $paypal_order_id . "/capture");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $accessToken
]);

$response = curl_exec($ch);
curl_close($ch);

$paypalResponse = json_decode($response, true);

if (($paypalResponse["status"] ?? "") !== "COMPLETED") {
    http_response_code(400);
    echo json_encode([
        "message" => "Payment capture failed",
        "paypal_response" => $paypalResponse
    ]);
    exit;
}

$stmt = $conn->prepare("
    UPDATE payments
    SET status = 'completed'
    WHERE payment_id = ?
");

$stmt->bind_param("i", $payment_id);
$stmt->execute();

$stmt = $conn->prepare("
    SELECT 
        p.amount,
        p.currency,
        u.first_name,
        u.last_name,
        u.email
    FROM payments p
    JOIN users u ON p.user_id = u.user_id
    WHERE p.payment_id = ?
");

$stmt->bind_param("i", $payment_id);
$stmt->execute();

$result = $stmt->get_result();
$paymentData = $result->fetch_assoc();

$email_sent = false;

if ($paymentData) {
    $email_sent = sendPaymentEmail(
        $paymentData["email"],
        $paymentData["first_name"] . " " . $paymentData["last_name"],
        $paymentData["amount"],
        $paymentData["currency"],
        $payment_id
    );
}

echo json_encode([
    "message" => "Payment completed successfully",
    "payment_id" => (int)$payment_id,
    "email_sent" => $email_sent,
    "paypal_response" => $paypalResponse
]);