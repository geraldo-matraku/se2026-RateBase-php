<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    http_response_code(200);
    echo json_encode(["message" => "Already logged out"]);
    exit;
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

http_response_code(200);
echo json_encode([
    "message" => "Logout success"
]);
?>