<?php
session_start();
include('../connect.php');

if (!isset($_GET['asset_id'])) {
    die(json_encode(['success' => false, 'message' => 'No asset ID provided']));
}

$assetId = (int)$_GET['asset_id'];
$query = "SELECT * FROM assets WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $assetId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die(json_encode(['success' => false, 'message' => 'Asset not found']));
}

$asset = $result->fetch_assoc();
echo json_encode(['success' => true, 'data' => $asset]);
?>