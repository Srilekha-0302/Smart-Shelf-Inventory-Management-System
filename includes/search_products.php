<?php
require 'db.php';

header('Content-Type: application/json');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT name FROM products WHERE name LIKE CONCAT('%', ?, '%') ORDER BY name ASC LIMIT 10");
$stmt->bind_param("s", $q);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = ['name' => $row['name']];
}

echo json_encode($suggestions);
?>
