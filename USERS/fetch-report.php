<?php
require '../connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    exit("Invalid request.");
}

$reportId = intval($_GET['id']);
$stmt = $conn->prepare("SELECT file_name FROM archives WHERE id = ?");
$stmt->bind_param("i", $reportId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    exit("Report not found.");
}

$row = $result->fetch_assoc();
$fileName = $row['file_name'];
$filePath = "../uploads/" . $fileName;

// Convert relative to absolute path
$publicUrl = "/uploads/" . rawurlencode($fileName);

// Confirm the file exists physically
if (!file_exists($filePath)) {
    http_response_code(404);
    exit("File not found.");
}

// Redirect the browser to the actual file
header("Location: $publicUrl");
exit();
?>
