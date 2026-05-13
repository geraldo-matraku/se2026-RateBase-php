<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header("Content-Type: application/json; charset=UTF-8");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "message" => "Unauthorized"
    ]);
    exit;
}

$product_id = $data['product_id']  ?? null;
$user_id= $_SESSION['user_id'];
$rating = $data['rating']  ?? null;
$comment = $data['comment']  ?? null;
$image = $data['image'] ?? null;


if (!isset($product_id, $rating, $user_id) || $comment === '') {
    http_response_code(400);
    echo json_encode([
        "message" => "All fields are required"
    ]);
    exit;
}

if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid rating"]);
    exit;

}


$stmt = $conn->prepare(
    "INSERT INTO reviews (product_id, user_id, rating, comment, image)
     VALUES (?, ?, ?, ?, ?)"
);

$stmt->bind_param("iiiss", $product_id,$user_id ,$rating, $comment, $image);

if ($stmt->execute()) {
    $review_id = $stmt->insert_id;

    http_response_code(201);
    echo json_encode([
        "message" => "Review created successfully",
        "review" => [
            "review_id" => $review_id,
            "product_id" => $product_id,
            "user_id" => $user_id,
            "rating" => $rating,
            "comment" => $comment,
            "image" => $image
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "message" => "Failed to create review"
    ]);
}