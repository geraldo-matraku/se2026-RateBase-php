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

$first_name = $data['first_name'] ?? '';
$last_name  = $data['last_name'] ?? '';
$email      = $data['email'] ?? '';
$password   = $data['password'] ?? '';


if (!$first_name || !$last_name || !$email || !$password) {
    http_response_code(400);
    echo json_encode([
        "message" => "All fields are required"
    ]);
    exit;
}

$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    http_response_code(409);
    echo json_encode([
        "message" => "Email already exists"
    ]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare("
    INSERT INTO users (first_name, last_name, email, password, role)
    VALUES (?, ?, ?, ?, 'user')
");

$stmt->bind_param("ssss", $first_name, $last_name, $email, $hashedPassword);

if ($stmt->execute()) {

    $user_id = $stmt->insert_id;

    $_SESSION['user_id'] = $user_id;
    $_SESSION['role'] = 'user';

    http_response_code(201);
    echo json_encode([
        "message" => "User registered successfully",
        
    ]);

} else {
    http_response_code(500);
    echo json_encode([
        "message" => "Registration failed"
    ]);
}