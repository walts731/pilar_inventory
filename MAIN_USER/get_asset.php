<?php
require '../connect.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No asset ID provided']);
    exit;
}

$assetId = $conn->real_escape_string($_GET['id']);

$query = "
    SELECT a.asset_name, a.quantity, a.last_updated,
           c.category_name, o.office_name
    FROM assets a
    JOIN categories c ON a.category = c.id
    JOIN offices o ON a.office = o.id
    WHERE a.id = '$assetId'
    LIMIT 1
";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $asset = $result->fetch_assoc();
    echo json_encode(['success' => true, 'asset' => $asset]);
} else {
    echo json_encode(['success' => false, 'message' => 'Asset not found']);
}
