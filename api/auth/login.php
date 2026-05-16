<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    http_response_code(404);
    echo json_encode(["message" => "User not found"]);
    exit;
}

if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(["message" => "Wrong password"]);
    exit;
}

$_SESSION['user_id'] = $user['user_id'];
$_SESSION['role'] = $user['role'];

http_response_code(200);
echo json_encode([
    "message" => "Login success",
   
]);