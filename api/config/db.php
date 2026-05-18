<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sistem_vleresimi_produktesh";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "DB connection failed"]);
    exit;
}
?>