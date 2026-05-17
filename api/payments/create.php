<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";
include __DIR__ . "/../config/paypal.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$data   = json_decode(file_get_contents("php://input"), true);
$amount = $data["amount"] ?? 0;
$user_id = $_SESSION['user_id'];

if (!$amount) {
    http_response_code(400);
    echo json_encode(["message" => "amount is required"]);
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

$payload = [
    "intent" => "CAPTURE",
    "purchase_units" => [
        [
            "amount" => [
                "currency_code" => "USD",
                "value" => number_format($amount, 2, ".", "")
            ]
        ]
    ]
];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . "/v2/checkout/orders");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $accessToken
]);

$response = curl_exec($ch);
curl_close($ch);

$order = json_decode($response, true);

if (!isset($order["id"])) {
    http_response_code(500);
    echo json_encode([
        "message" => "Failed to create PayPal order",
        "error"   => $order
    ]);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO payments (user_id, amount, currency, description, status, paypal_order_id)
    VALUES (?, ?, 'USD', 'PayPal Payment', 'pending', ?)
");

$stmt->bind_param("ids", $user_id, $amount, $order["id"]);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["message" => "Failed to save payment"]);
    exit;
}

echo json_encode([
    "message" => "PayPal order created successfully",
    "payment" => [
        "payment_id"  => $stmt->insert_id,
        "user_id"     => (int) $user_id,
        "amount"      => (float) $amount,
        "currency"    => "USD",
        "description" => "PayPal Payment",
        "status"      => "pending"
    ],
    "paypal_order" => $order
]);