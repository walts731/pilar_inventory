<?php
session_start();
require '../connect.php'; // Database connection file

// Check if POST request contains 'id'
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    exit("Invalid request.");
}

$reportId = intval($_POST['id']);
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
$filePath = "../uploads/" . $fileName; // Path to the uploaded report

// Check if the file exists
if (!file_exists($filePath)) {
    http_response_code(404);
    exit("File not found at: " . $filePath);
}

// Output file (use appropriate headers for the file type)
header("Content-Type: application/octet-stream");
header("Content-Disposition: inline; filename=\"" . basename($filePath) . "\"");
readfile($filePath);
exit();
?>
