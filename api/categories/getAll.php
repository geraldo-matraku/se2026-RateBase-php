<?php
include __DIR__ . "/../config/cors.php";
include __DIR__ . "/../config/session.php";
include __DIR__ . "/../config/db.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q !== '') {
    $search = "%" . $q . "%";

    $sql = "
        SELECT 
            c.category_id,
            c.name,
            c.description,
            c.image,
            COUNT(p.product_id) AS total_products
        FROM categories c
        LEFT JOIN products p ON p.category_id = c.category_id
        WHERE c.name LIKE ? OR c.description LIKE ?
        GROUP BY c.category_id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search, $search);

} else {
    $sql = "
        SELECT 
            c.category_id,
            c.name,
            c.description,
            c.image, 
            COUNT(p.product_id) AS total_products
        FROM categories c
        LEFT JOIN products p ON p.category_id = c.category_id
        GROUP BY c.category_id
    ";

    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $row['total_products'] = (int)$row['total_products'];
    $row['image'] = $row['image'] ? $row['image'] : null;
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data,
    "total_categories" => count($data)
]);