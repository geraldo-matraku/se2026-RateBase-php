<?php
include __DIR__ . "/../config/db.php"; 

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q !== '') {
   
    $search = "%" . $q . "%";
    $sql = "SELECT * FROM categories WHERE name LIKE ? OR description LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search, $search);
} else {
   
    $sql = "SELECT * FROM categories";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
   
    "data" => $data
]);