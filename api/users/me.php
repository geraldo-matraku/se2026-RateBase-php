<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/db.php";


header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}


$sessionName = session_name();
if (!isset($_COOKIE[$sessionName])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized: No session cookie found"
    ]);
    exit;
}


if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'read_and_close' => true
    ]);
}



if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized: Session is empty"
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];


try {
    $stmt = $conn->prepare("
        SELECT user_id, first_name, last_name, email, role 
        FROM users 
        WHERE user_id = ? 
        LIMIT 1
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "User not found in database"
        ]);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "user" => $user
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Internal Server Error"
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}