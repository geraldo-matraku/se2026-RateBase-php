<?php
include __DIR__ . "/../config/db.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Content-Type: application/json');

    echo json_encode([
        "status" => "error",
        "message" => "ID e pavlefshme"
    ]);

    exit;
}

$sql = "
    SELECT 
        c.category_id,
        c.name,
        c.description,
        c.image,
        COUNT(p.product_id) AS total_products
    FROM categories c
    LEFT JOIN products p 
        ON p.category_id = c.category_id
    WHERE c.category_id = ?
    GROUP BY c.category_id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {

    header('Content-Type: application/json');

    echo json_encode([
        "status" => "error",
        "message" => "Kategoria nuk u gjet"
    ]);

    exit;
}

$row = $result->fetch_assoc();

$row['total_products'] = (int)$row['total_products'];
$row['image'] = $row['image'] ? $row['image'] : null;

header('Content-Type: application/json');

echo json_encode([
    "status" => "success",
    "data" => $row
]);