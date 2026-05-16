<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/sendPaymentEmail.php";

header("Content-Type: application/json");

$sent = sendPaymentEmail(
    "EMAILI KTU@gmail.com",
    "Test User",
    1.50,
    "USD",
    1
);

if ($sent) {
    echo json_encode(["message" => "Email sent successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Email failed"]);
}